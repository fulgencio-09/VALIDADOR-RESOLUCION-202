"""Verifica que el catálogo RPED pueda reproducirse desde el Excel oficial.

Uso:
  python tools/verify_res202_catalog.py /ruta/Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx

Resultado esperado para la versión v8 entregada al proyecto:
  119 variables, 395 reglas únicas, 377 errores y 18 warnings.
"""
from __future__ import annotations
import re
import sys
from pathlib import Path
import pandas as pd

EXPECTED_VARIABLES = 119
EXPECTED_RULES = 395
EXPECTED_ERRORS = 377
EXPECTED_WARNINGS = 18


def main(source: str) -> None:
    path = Path(source)
    df = pd.read_excel(path, sheet_name="Lineamientos RPED", header=6)
    variables = df[pd.to_numeric(df["No..1"], errors="coerce").notna()]
    codes = []
    for value in df["Código del error o warning"].dropna():
        codes.extend(re.findall(r"\b(?:Error|Warning)\d+\b", str(value)))
    unique = sorted(set(codes))
    errors = sum(code.startswith("Error") for code in unique)
    warnings = sum(code.startswith("Warning") for code in unique)
    assert len(variables) == EXPECTED_VARIABLES, len(variables)
    assert len(unique) == EXPECTED_RULES, len(unique)
    assert errors == EXPECTED_ERRORS, errors
    assert warnings == EXPECTED_WARNINGS, warnings
    print(f"OK variables={len(variables)} rules={len(unique)} errors={errors} warnings={warnings}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        raise SystemExit("Uso: python tools/verify_res202_catalog.py <lineamientos.xlsx>")
    main(sys.argv[1])
