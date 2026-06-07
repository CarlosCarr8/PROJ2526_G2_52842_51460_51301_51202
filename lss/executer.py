import json
from parser import parser

texto = input("LSS> ")

resultado = parser.parse(texto)

print(
    json.dumps(
        resultado,
        default=str
    )
)