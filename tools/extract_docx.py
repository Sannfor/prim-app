# -*- coding: utf-8 -*-
"""Ekstrak teks dari berkas .docx tanpa dependensi luar.

Membaca word/document.xml langsung, lalu menuliskan teks beserta penanda gaya
(heading dan tabel) sehingga susunan bab dapat ditelusuri.
"""

from __future__ import annotations

import re
import sys
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

W = "{http://schemas.openxmlformats.org/wordprocessingml/2006/main}"


def text_of(element) -> str:
    """Gabungkan seluruh teks di dalam sebuah elemen (paragraf/sel)."""
    parts = []

    for node in element.iter():
        if node.tag == f"{W}t" and node.text:
            parts.append(node.text)

        elif node.tag == f"{W}tab":
            parts.append("\t")

        elif node.tag == f"{W}br":
            parts.append(" ")

    return re.sub(r"[ \t]+", " ", "".join(parts)).strip()


def style_of(paragraph) -> str:
    """Ambil nama gaya sebuah paragraf."""
    properties = paragraph.find(f"{W}pPr")

    if properties is None:
        return ""

    style = properties.find(f"{W}pStyle")

    return style.get(f"{W}val", "") if style is not None else ""


def table_to_text(table) -> str:
    """Ubah sebuah tabel menjadi teks bergaya Markdown."""
    rows = []

    for row in table.findall(f"{W}tr"):
        cells = [text_of(cell) for cell in row.findall(f"{W}tc")]
        rows.append("| " + " | ".join(cells) + " |")

    if not rows:
        return ""

    header = rows[0]
    separator = "|" + "---|" * (header.count("|") - 1)

    return "\n".join([header, separator, *rows[1:]])


def walk(body, out: list[str], counters: dict) -> None:
    """Telusuri isi dokumen secara berurutan."""
    for child in body:
        if child.tag == f"{W}p":
            text = text_of(child)
            style = style_of(child)

            if not text:
                out.append("")
                continue

            match = re.match(r"Heading(\d)", style)

            if match:
                level = int(match.group(1))
                out.append("")
                out.append("#" * level + " " + text)
                counters["headings"] += 1
            elif style.lower().startswith("title"):
                out.append("")
                out.append("# " + text)
            else:
                out.append(text)

        elif child.tag == f"{W}tbl":
            counters["tables"] += 1
            out.append("")
            out.append(f"<!-- TABEL {counters['tables']} -->")
            out.append(table_to_text(child))
            out.append("")


def main() -> None:
    source = Path(sys.argv[1])
    destination = Path(sys.argv[2])

    with zipfile.ZipFile(source) as archive:
        xml = archive.read("word/document.xml")

    root = ET.fromstring(xml)
    body = root.find(f"{W}body")

    out: list[str] = []
    counters = {"tables": 0, "headings": 0}

    walk(body, out, counters)

    cleaned: list[str] = []
    blank = 0

    for line in out:
        if line == "":
            blank += 1
            if blank > 1:
                continue
        else:
            blank = 0

        cleaned.append(line)

    destination.write_text("\n".join(cleaned), encoding="utf-8")

    words = sum(len(line.split()) for line in cleaned if not line.startswith("#"))
    print(f"heading : {counters['headings']}")
    print(f"tabel   : {counters['tables']}")
    print(f"baris   : {len(cleaned)}")
    print(f"kata    : {words}")
    print(f"keluaran: {destination}")


if __name__ == "__main__":
    main()
