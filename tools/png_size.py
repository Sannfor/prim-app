# -*- coding: utf-8 -*-
"""Baca ukuran sebuah berkas PNG tanpa dependensi luar."""

from __future__ import annotations

import struct
import sys
from pathlib import Path


def png_size(path: Path) -> tuple[int, int]:
    with path.open("rb") as handle:
        header = handle.read(24)

    if header[:8] != b"\x89PNG\r\n\x1a\n":
        raise ValueError("bukan berkas PNG")

    width, height = struct.unpack(">II", header[16:24])

    return width, height


for arg in sys.argv[1:]:
    file = Path(arg)

    if not file.exists():
        print(f"HILANG  {file.name}")
        continue

    width, height = png_size(file)
    print(f"{width:>5} x {height:<5} px  {file.stat().st_size / 1024:>8.1f} KB  {file.name}")
