/*
 | Tangkap layar halaman web secara penuh (full page) lewat Chrome DevTools Protocol.
 |
 | Cara pakai:
 |   node tools/screenshot.mjs --url=http://127.0.0.1:8000/katalog --out=docs/screens/4-1-katalog.png
 |
 | Beberapa halaman sekaligus (Chrome dibuka sekali saja):
 |   node tools/screenshot.mjs --profile=storage/app/chrome-profile \
 |     --url=http://127.0.0.1:8000/ --out=docs/screens/4-5-beranda.png \
 |     --url=http://127.0.0.1:8000/katalog --out=docs/screens/4-1-katalog.png
 |
 | Halaman yang perlu login: tambahkan --as=<email>. Skrip akan masuk lebih dahulu
 | lewat permintaan HTTP, lalu memakai cookie sesinya untuk seluruh tangkapan.
 | Kata sandi bawaan adalah "password" dan dapat diganti dengan --password=<sandi>.
 |   node tools/screenshot.mjs --as=admin@prim.com --url=http://127.0.0.1:8000/admin --out=admin.png
 |
 | Bila langganan Anda memakai proteksi tambahan, jalankan --login untuk masuk
 | secara manual pada jendela Chrome yang terbuka, lalu tekan Enter di terminal.
 */

import { spawn } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, writeFileSync, rmSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { createInterface } from 'node:readline';

const CHROME_CANDIDATES = [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    'C:/Program Files/Microsoft/Edge/Application/msedge.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
];

const PORT = 9333;

/** Baca argumen berbentuk --kunci=nilai, dan --kunci untuk penanda. */
function parseArgs(argv) {
    const pairs = [];

    for (const arg of argv) {
        if (!arg.startsWith('--')) continue;

        const [key, ...rest] = arg.slice(2).split('=');
        pairs.push([key, rest.join('=')]);
    }

    const options = { url: [], out: [], width: 1440, profile: null, login: false, wait: 1200, as: null, password: 'password' };

    for (const [key, value] of pairs) {
        if (key === 'url') options.url.push(value);
        else if (key === 'out') options.out.push(value);
        else if (key === 'width') options.width = Number(value);
        else if (key === 'profile') options.profile = value;
        else if (key === 'wait') options.wait = Number(value);
        else if (key === 'as') options.as = value;
        else if (key === 'password') options.password = value;
        else if (key === 'token') options.token = value;
        else if (key === 'login') options.login = true;
    }

    return options;
}

function findChrome() {
    const found = CHROME_CANDIDATES.find((path) => existsSync(path));

    if (!found) {
        throw new Error('Chrome/Edge tidak ditemukan. Jalankan dengan --chrome=<path>.');
    }

    return found;
}

/** Tunggu sampai port debug siap menerima koneksi. */
async function waitForPort(timeoutMs = 30000) {
    const deadline = Date.now() + timeoutMs;

    while (Date.now() < deadline) {
        try {
            const response = await fetch(`http://127.0.0.1:${PORT}/json/version`);

            if (response.ok) return await response.json();
        } catch {
            // belum siap
        }

        await new Promise((r) => setTimeout(r, 400));
    }

    throw new Error('Chrome tidak merespons pada port debug.');
}

/** Klien WebSocket sederhana untuk DevTools Protocol. */
class Cdp {
    constructor(socket) {
        this.socket = socket;
        this.nextId = 1;
        this.pending = new Map();

        socket.addEventListener('message', (event) => {
            const message = JSON.parse(event.data);
            const waiter = this.pending.get(message.id);

            if (!waiter) return;

            this.pending.delete(message.id);

            if (message.error) waiter.reject(new Error(message.error.message));
            else waiter.resolve(message.result);
        });
    }

    static async attach(webSocketDebuggerUrl) {
        const socket = new WebSocket(webSocketDebuggerUrl);

        await new Promise((resolve, reject) => {
            socket.addEventListener('open', resolve, { once: true });
            socket.addEventListener('error', () => reject(new Error('gagal membuka WebSocket CDP')), { once: true });
        });

        return new Cdp(socket);
    }

    send(method, params = {}) {
        const id = this.nextId++;

        return new Promise((resolve, reject) => {
            this.pending.set(id, { resolve, reject });
            this.socket.send(JSON.stringify({ id, method, params }));
        });
    }

    close() {
        this.socket.close();
    }
}

/** Buka tab baru dan kembalikan target id beserta URL WebSocket-nya. */
async function openTab(target) {
    const response = await fetch(`http://127.0.0.1:${PORT}/json/new?${encodeURIComponent(target)}`, {
        method: 'PUT',
    });

    return await response.json();
}

/**
 * Masuk ke aplikasi memakai rute bantuan pengembangan.
 *
 * Rute /_dev/login hanya didaftarkan saat APP_ENV=local dan dilindungi token
 * yang dibuat per proses. Alur ini dipakai karena formulir masuk aplikasi
 * berjalan di atas Livewire: mengirim formulir secara langsung dari skrip
 * memerlukan checksum snapshot Livewire yang sah, sedangkan rute bantuan
 * menghasilkan sesi masuk lewat mekanisme Laravel yang sama.
 */
async function signIn(baseUrl, email, token) {
    if (!token) {
        throw new Error('token rute bantuan belum diberikan (pakai --token=...)');
    }

    const url = `${baseUrl}/_dev/login?token=${encodeURIComponent(token)}&email=${encodeURIComponent(email)}`;
    const response = await fetch(url, { redirect: 'manual' });

    const cookies = (response.headers.getSetCookie?.() ?? [])
        .map((cookie) => cookie.split(';')[0])
        .join('; ');

    if (!cookies) {
        throw new Error('rute bantuan tidak mengembalikan cookie sesi; periksa email dan token');
    }

    // Pastikan sesi benar-benar sudah masuk.
    const check = await fetch(`${baseUrl}/profil/pesanan`, {
        headers: { Cookie: cookies },
        redirect: 'manual',
    });

    if (check.status !== 200) {
        throw new Error(`sesi belum terbentuk (status ${check.status})`);
    }

    return cookies;
}

async function closeTab(id) {
    try {
        await fetch(`http://127.0.0.1:${PORT}/json/close/${id}`);
    } catch {
        // abaikan
    }
}

async function capture({ url, out, width, wait, cookie, host }) {
    const tab = await openTab('about:blank');
    const cdp = await Cdp.attach(tab.webSocketDebuggerUrl);

    try {
        await cdp.send('Page.enable');
        await cdp.send('Runtime.enable');
        await cdp.send('Network.enable');

        // Sisipkan cookie sesi bila tersedia, agar halaman terlindungi terbuka.
        if (cookie) {
            for (const pair of cookie.split('; ')) {
                const index = pair.indexOf('=');

                if (index <= 0) continue;

                await cdp.send('Network.setCookie', {
                    name: pair.slice(0, index),
                    value: pair.slice(index + 1),
                    domain: host,
                    path: '/',
                });
            }
        }

        // Lebar tetap, tinggi viewport sementara; nanti diukur ulang.
        await cdp.send('Emulation.setDeviceMetricsOverride', {
            width,
            height: 900,
            deviceScaleFactor: 1,
            mobile: false,
        });

        await cdp.send('Page.navigate', { url });

        // Tunggu halaman selesai dimuat.
        await new Promise((resolve) => {
            const timeout = setTimeout(resolve, 15000);

            cdp.socket.addEventListener('message', (event) => {
                const message = JSON.parse(event.data);

                if (message.method === 'Page.loadEventFired') {
                    clearTimeout(timeout);
                    resolve();
                }
            });
        });

        // Beri waktu aset late-loading (Livewire, gambar) selesai.
        await new Promise((r) => setTimeout(r, wait));

        // Ukur tinggi dokumen sebenarnya.
        const metrics = await cdp.send('Page.getLayoutMetrics');
        const size = metrics.cssContentSize ?? metrics.contentSize;
        const height = Math.ceil(size.height);

        // Setel viewport setinggi dokumen, lalu tangkap melebihi viewport.
        await cdp.send('Emulation.setDeviceMetricsOverride', {
            width,
            height,
            deviceScaleFactor: 1,
            mobile: false,
        });

        const shot = await cdp.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
            fromSurface: true,
        });

        mkdirSync(dirname(resolve(out)), { recursive: true });
        writeFileSync(out, Buffer.from(shot.data, 'base64'));

        const bytes = readFileSync(out).length;

        console.log(`OK   ${width}x${height}  ${(bytes / 1024).toFixed(0)} KB  ${out}`);
    } finally {
        cdp.close();
        await closeTab(tab.id);
    }
}

async function main() {
    const options = parseArgs(process.argv.slice(2));

    if (options.url.length === 0) {
        console.error('Tidak ada --url yang diberikan.');
        process.exit(1);
    }

    const chrome = findChrome();
    const profile = resolve(options.profile ?? 'storage/app/chrome-profile');

    mkdirSync(profile, { recursive: true });

    // Jalankan Chrome dengan port debug. CDP berjalan lewat WebSocket (TCP),
    // bukan named pipe, sehingga tidak terhalang pembatasan sandbox.
    const child = spawn(chrome, [
        `--remote-debugging-port=${PORT}`,
        `--user-data-dir=${profile}`,
        '--no-first-run',
        '--no-default-browser-check',
        '--disable-gpu',
        '--hide-scrollbars',
        '--force-device-scale-factor=1',
        options.login ? '--new-window' : '--headless=new',
        'about:blank',
    ], { stdio: 'ignore', detached: false });

    const version = await waitForPort();
    console.log(`Chrome ${version.Browser} siap.`);

    if (options.login) {
        console.log('\nJendela Chrome dibuka. Silakan MASUK ke aplikasi pada jendela itu,');
        console.log('lalu kembali ke terminal ini dan tekan Enter untuk melanjutkan.\n');

        await new Promise((resolve) => {
            const rl = createInterface({ input: process.stdin, output: process.stdout });
            rl.question('Tekan Enter bila sudah masuk... ', () => {
                rl.close();
                resolve();
            });
        });
    }

    let activeCookie = null;
    let signedInHost = null;

    for (let i = 0; i < options.url.length; i++) {
        const url = options.url[i];
        const out = options.out[i] ?? `storage/app/screenshot-${i + 1}.png`;

        let cookie = null;
        let host = null;

        try {
            const parsed = new URL(url);
            host = parsed.hostname;

            if (options.as && parsed.hostname !== signedInHost) {
                cookie = await signIn(parsed.origin, options.as, options.token);
                signedInHost = parsed.hostname;
                console.log(`Masuk sebagai ${options.as}.`);
            } else if (options.as) {
                cookie = activeCookie;
            }

            if (cookie) activeCookie = cookie;
        } catch (error) {
            console.error(`GAGAL menyiapkan sesi untuk ${url} — ${error.message}`);
            continue;
        }

        try {
            await capture({ url, out, width: options.width, wait: options.wait, cookie, host });
        } catch (error) {
            console.error(`GAGAL ${url} — ${error.message}`);
        }
    }

    child.kill();

    setTimeout(() => {
        try {
            rmSync(profile, { recursive: true, force: true });
        } catch {
            // profil mungkin masih dipakai; abaikan
        }
    }, 500);
}

main().catch((error) => {
    console.error(error.message);
    process.exit(1);
});
