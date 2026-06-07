import sys
import json
from parser import parser

texto = sys.stdin.read().strip()

if texto == "" and len(sys.argv) > 1:
    texto = sys.argv[1]

try:
    resultado = parser.parse(texto)

    print(
        json.dumps(
            resultado,
            default=str,
            ensure_ascii=False
        )
    )

except Exception as e:
    print(
        json.dumps(
            {
                "comando": "erro",
                "mensagem": str(e)
            },
            ensure_ascii=False
        )
    )