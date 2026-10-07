/*
 | Ukur posisi elemen pada halaman yang sudah dimuat, lewat Chrome DevTools Protocol.
 |
 | Dipakai untuk memeriksa tata letak: memastikan bilah samping dan isi halaman
 | tidak saling tumpang tindih, serta mengukur lebar kontainer sebenarnya.
 |
 | Cara pakai (dari dalam folder prim):
 |   node tools/ukur-layout.mjs --url=http://127.0.0.1:8000/langganan --as=andi@prim.test --token=<token>
 */

import { spawn } from 'node:child_process';
import { existsSync, rmSync } from 'node:fs';
import { resolve } from 'node:path';

const CHROME = [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
].find((p) => existsSync(p));

if (!CHROME) {
    throw new Error('Chrome tidak ditemukan.');
}

const PORT = 9444;
const PROFILE = resolve('storage/app/chrome-ukur');

function arg(key, fallback = null) {
    const found = process.argv.find((a) => a.startsWith(`--${key}=`));

    return found ? found.slice(key.length + 3) : fallback;
}

const target = arg('url', 'http://127.0.0.1:8000/');
const email = arg('as');
const token = arg('token');
const password = arg('password', 'password');
const width = Number(arg('width', '1440'));

async function waitForPort(timeoutMs = 30000) {
    const batas = Date.now() + timeoutMs;

    while (Date.now() < batas) {
        try {
            const r = await fetch(`http://127.0.0.1:${PORT}/json/version`);

            if (r.ok) return;
        } catch {
            // belum siap
        }

        await new Promise((r) => setTimeout(r, 250));
    }

    throw new Error('Port debug Chrome tidak siap.');
}

function connect(url) {
    return new Promise((resolvePromise, reject) => {
        const socket = new WebSocket(url);
        let id = 0;
        const pending = new Map();

        socket.addEventListener('message', (event) => {
            const message = JSON.parse(event.data);

            if (message.id && pending.has(message.id)) {
                const { resolve: res, reject: rej } = pending.get(message.id);
                pending.delete(message.id);

                message.error ? rej(new Error(message.error.message)) : res(message.result);
            }
        });

        socket.addEventListener('error', reject);

        socket.addEventListener('open', () => {
            resolvePromise({
                send(method, params = {}) {
                    return new Promise((res, rej) => {
                        const messageId = ++id;
                        pending.set(messageId, { resolve: res, reject: rej });
                        socket.send(JSON.stringify({ id: messageId, method, params }));
                    });
                },
                close: () => socket.close(),
            });
        });
    });
}

const child = spawn(CHROME, [
    '--headless=new',
    `--remote-debugging-port=${PORT}`,
    `--user-data-dir=${PROFILE}`,
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-gpu',
    '--hide-scrollbars',
]);

try {
    await waitForPort();

    let cookie = null;

    if (email) {
        // Rute bantuan mengembalikan JSON; cookie sesi diambil dari header Set-Cookie.
        const jawaban = await fetch(`${new URL(target).origin}/_dev/login?token=${token}&email=${email}`);

        if (!jawaban.ok) {
            throw new Error(`Gagal masuk sebagai ${email} (status ${jawaban.status}).`);
        }

        const setCookie = jawaban.headers.getSetCookie?.() ?? [];
        const sesi = setCookie
            .map((c) => c.split(';')[0])
            .filter((c) => c.includes('session='));

        cookie = sesi.join('; ') || null;

        if (cookie === null) {
            throw new Error('Cookie sesi tidak ditemukan pada jawaban login.');
        }
    }

    const tab = await (await fetch(`http://127.0.0.1:${PORT}/json/new?about:blank`, { method: 'PUT' })).json();
    const cdp = await connect(tab.webSocketDebuggerUrl);

    await cdp.send('Page.enable');
    await cdp.send('Runtime.enable');
    await cdp.send('Network.enable');

    await cdp.send('Emulation.setDeviceMetricsOverride', {
        width, height: 1000, deviceScaleFactor: 1, mobile: false,
    });

    if (cookie) {
        const host = new URL(target).hostname;

        for (const pair of cookie.split('; ')) {
            const i = pair.indexOf('=');

            if (i <= 0) continue;

            await cdp.send('Network.setCookie', {
                name: pair.slice(0, i), value: pair.slice(i + 1), domain: host, path: '/',
            });
        }
    }

    await cdp.send('Page.navigate', { url: target });
    await new Promise((r) => setTimeout(r, 3500));

    const ekspresi = `(() => {
        const ukur = (sel) => {
            const el = document.querySelector(sel);
            if (!el) return null;
            const r = el.getBoundingClientRect();
            return { kiri: Math.round(r.left), kanan: Math.round(r.right), lebar: Math.round(r.width) };
        };

        const kandidat = [
            ['header  ', 'body > header'],
            ['wadah   ', 'body > div'],
            ['grid    ', 'main'] ,
            ['aside   ', 'aside'],
            ['isi     ', 'main'],
            ['judul   ', 'main h1'],
        ];

        const hasil = {};
        for (const [nama, sel] of kandidat) hasil[nama.trim()] = ukur(sel);

        // Kesejajaran tombol "Pesan" dan tinggi kartu pada baris pertama.
        const tombol = [...document.querySelectorAll('article .prim-btn')]
            .slice(0, 8)
            .map((el) => {
                const r = el.getBoundingClientRect();
                return { atas: Math.round(r.top), bawah: Math.round(r.bottom) };
            });

        const kartu = [...document.querySelectorAll('article')]
            .slice(0, 8)
            .map((el) => Math.round(el.getBoundingClientRect().height));

        // Lebar dokumen dan ada tidaknya gulir mendatar.
        return {
            viewport: window.innerWidth,
            dokumen: document.documentElement.scrollWidth,
            gulirMendatar: document.documentElement.scrollWidth > window.innerWidth,
            elemen: hasil,
            tinggiKartu: kartu,
            posisiTombol: tombol,
        };
    })()`;

    const jawab = await cdp.send('Runtime.evaluate', { expression: ekspresi, returnByValue: true });
    const data = jawab.result.value;

    console.log(`URL        : ${target}`);
    console.log(`Viewport   : ${data.viewport}px`);
    console.log(`Lebar dok  : ${data.dokumen}px${data.gulirMendatar ? '  <- ADA GULIR MENDATAR' : ''}`);
    console.log('');

    for (const [nama, kotak] of Object.entries(data.elemen)) {
        if (!kotak) {
            console.log(`  ${nama}: (tidak ditemukan)`);

            continue;
        }

        console.log(`  ${nama}: kiri=${kotak.kiri}  kanan=${kotak.kanan}  lebar=${kotak.lebar}`);
    }

    if (data.tinggiKartu?.length) {
        console.log('');
        console.log(`  tinggi kartu  : ${data.tinggiKartu.join(', ')}`);

        const atas = data.posisiTombol.map((t) => t.atas);
        const sejajar = new Set(atas).size === 1;

        console.log(`  tombol "Pesan": ${atas.join(', ')}`);
        console.log(`  sejajar       : ${sejajar ? 'YA' : 'TIDAK'}`);
    }

    cdp.close();
} finally {
    child.kill();
    setTimeout(() => rmSync(PROFILE, { recursive: true, force: true }), 500);
}
