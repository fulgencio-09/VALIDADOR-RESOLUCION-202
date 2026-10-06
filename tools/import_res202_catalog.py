"""Importa el catálogo de Resolución 202 desde el Excel oficial de lineamientos.

Uso:
    python tools/import_res202_catalog.py /ruta/Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx

Genera:
    database/catalog/variables_rped.csv
    database/catalog/validation_rules_rped.csv

La hoja RPED contiene las 119 variables del Anexo Técnico 1 y las validaciones
asociadas. Las filas sin número de variable se consideran reglas adicionales
de la variable anterior y se conservan por su código de error/warning.
"""
from __future__ import annotations
import re
import sys
from pathlib import Path
import pandas as pd

ROOT = Path(__file__).resolve().parents[1]
CATALOG = ROOT / "database" / "catalog"


def clean(value):
    if pd.isna(value):
        return ""
    return str(value).strip().replace("\r", " ").replace("\n", " ")


def main(source: str) -> None:
    xlsx = Path(source)
    if not xlsx.exists():
        raise SystemExit(f"No existe el archivo: {xlsx}")

    df = pd.read_excel(xlsx, sheet_name="Lineamientos RPED", header=6)
    variable_col = "No..1"

    variables = df[pd.to_numeric(df[variable_col], errors="coerce").notna()].copy()
    variables = variables[[variable_col, "GRUPO", "NOMBRE DE LA VARIABLE.1", "LONGITUD", "TIPO", "VALORES PERMITIDOS.1", "USO DE VALORES PERMITIDOS"]]
    variables[variable_col] = variables[variable_col].astype(int)
    variables.to_csv(CATALOG / "variables_rped.csv", index=False, encoding="utf-8-sig")

    rows = []
    for _, row in df.iterrows():
        raw_codes = clean(row.get("Código del error o warning"))
        codes = re.findall(r"\b(?:Error|Warning)\d+\b", raw_codes)
        for code in codes:
            rows.append({
                "code": code,
                "severity": "warning" if code.startswith("Warning") else "error",
                "source_variable": int(row[variable_col]) if not pd.isna(row[variable_col]) else "",
                "description": clean(row.get("Descripción del error o warning")),
                "related_variables": clean(row.get("Variables relacionadas")),
                "validation": clean(row.get("VALIDACIONES")),
            })

    rules = pd.DataFrame(rows).drop_duplicates(subset=["code", "source_variable", "description", "validation"])
    rules.to_csv(CATALOG / "validation_rules_rped.csv", index=False, encoding="utf-8-sig")
    print(f"Variables: {len(variables)}")
    print(f"Reglas/ocurrencias: {len(rules)}")
    print(f"Códigos únicos: {rules['code'].nunique()}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        raise SystemExit("Uso: python tools/import_res202_catalog.py <lineamientos.xlsx>")
    main(sys.argv[1])
