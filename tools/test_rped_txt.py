"""Prueba estructural y reglas RPED seleccionadas sobre un TXT real.

Uso:
  python tools/test_rped_txt.py /ruta/440900022701_30092026.txt
"""
from __future__ import annotations
import re
import sys
from pathlib import Path

EXPECTED_RECORDS = 431
EXPECTED_FIELDS = 119


def check(path: Path) -> dict[str, int]:
    lines = path.read_text(encoding="utf-8").splitlines()
    assert len(lines) == EXPECTED_RECORDS + 1, len(lines)
    control = lines[0].split("|")
    assert control == ["1", "EPSS41", "2026-09-01", "2026-09-30", "431"], control

    records = [line.split("|") for line in lines[1:]]
    assert all(row[0] == "2" for row in records)
    assert all(len(row) == EXPECTED_FIELDS for row in records)
    assert all(row[1] == str(i) for i, row in enumerate(records, 1))

    # Reglas representativas verificables sin catálogos externos.
    violations = {
        "Error030": sum(r[14] in {"1", "2", "21"} and r[10] != "F" for r in records),
        "Error041": sum(r[30] == "999" and r[29] != "1800-01-01" for r in records),
        "Error043": sum(r[32] == "999" and r[31] != "1800-01-01" for r in records),
        "Error653": sum(r[113] not in {"1", "2", "3", "4", "21"} for r in records if r[113] != ""),
        "Error655": sum(r[114] not in {"0", "4", "5", "6", "21"} for r in records if r[114] != ""),
        "Error656": sum(r[115] != "0" for r in records),
        "Error657": sum(r[116] != "0" for r in records),
        "Error665": sum(r[117] not in {"0", "4", "5", "6", "21"} for r in records if r[117] != ""),
    }
    assert all(value == 0 for value in violations.values()), violations
    return {"records": len(records), **violations}


if __name__ == "__main__":
    if len(sys.argv) != 2:
        raise SystemExit("Uso: python tools/test_rped_txt.py <archivo.txt>")
    result = check(Path(sys.argv[1]))
    print("OK", result)
