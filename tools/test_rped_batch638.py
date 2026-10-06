from __future__ import annotations

import sys
from collections import Counter
from datetime import date
from pathlib import Path

CUTOFF=date(2026,9,30)
WILDCARDS={'1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'}
NO_DATA={'1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01'}

def age(r):
    b=date.fromisoformat(r[9]); return (CUTOFF.year-b.year)*12+CUTOFF.month-b.month-(CUTOFF.day<b.day)

def check(path: Path):
    lines=path.read_text(encoding='utf-8').splitlines(); records=[x.split('|') for x in lines[1:]]
    assert len(lines)==432 and len(records)==431 and lines[0]=='1|EPSS41|2026-09-01|2026-09-30|431'
    assert all(len(r)==119 for r in records)
    rules={
      'Error638':lambda r:r[10]=='F' and 120<=age(r)<=204 and r[103]=='1845-01-01',
      'Error639':lambda r:r[103]>'1900-01-01' and (r[104]=='0' or r[104]>='99'),
      'Error640':lambda r:r[10]=='F' and 120<=age(r)<=204 and r[103] in NO_DATA and r[104]!='998',
      'Error641':lambda r:r[103]=='1845-01-01' and r[104]!='0',
      'Error642':lambda r:r[105] not in WILDCARDS and r[105]>'2026-09-30',
      'Error643':lambda r:r[105] not in WILDCARDS and r[105]<=r[9],
      'Error645':lambda r:(r[106] in NO_DATA and r[107]!='998') or (r[107]=='998' and r[106] not in NO_DATA),
      'Error646':lambda r:r[106]=='1845-01-01' and (r[107]!='0' or age(r)>=348),
      'Error647':lambda r:r[108]!='1845-01-01',
      'Error649':lambda r:r[73]>'1900-01-01' and (r[109]=='0' or r[109]>='998' or age(r)<480 or r[10]!='M'),
      'Error650':lambda r:r[109]=='998' and r[73] not in NO_DATA,
      'Error651':lambda r:r[10]=='F' and (r[109]!='0' or r[73]!='1845-01-01'),
      'Error652':lambda r:r[10]=='M' and age(r)<480 and (r[109]!='0' or r[73]!='1845-01-01'),
    }
    counts=Counter(code for r in records for code,rule in rules.items() if rule(r))
    print(f'records={len(records)} rules={len(rules)}')
    for code in rules: print(f'{code}: {counts[code]}')

if __name__=='__main__':
    if len(sys.argv)!=2: raise SystemExit('Uso: python tools/test_rped_batch638.py <archivo.txt>')
    check(Path(sys.argv[1]))
