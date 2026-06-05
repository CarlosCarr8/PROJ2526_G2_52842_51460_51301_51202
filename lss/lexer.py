import ply.lex as lex
from datetime import datetime

reserved = {
    #comandos/ funções
    'reservar': 'RESERVAR',
    'cancelar': 'CANCELAR',
    'consulta': 'CONSULTA',
    'disponibilidade': 'DISPONIBILIDADE',
    'batch': 'BATCH',
    'def': 'DEF',
    'if': 'IF',
    'else': 'ELSE',
    #lógica
    'and': 'AND',
    'or': 'OR',
    'not': 'NOT',
    'true': 'TRUE',
    'false': 'FALSE',
    #atributos/ objetos
    'tipo': 'TIPO',
    'espaco': 'ESPACO',
    'data': 'DATA',
    'hora_inicio': 'HORA_INICIO',
    'hora_fim': 'HORA_FIM',
    'filtro': 'FILTRO',
    'periodo': 'PERIODO',
    'utilizadores': 'UTILIZADORES',
    'reserva_id': 'RESERVA_ID',
}

tokens = [

    # valores
    'STRING',
    'NUMBER',
    'DATE',
    'TIME',
    'ID',
    'NEWLINE',

    # símbolos
    'COLON',
    'LPAREN',
    'RPAREN',
    'COMMA',

    # operadores
    'EQUALS',
    'NOT_EQUALS',
    'GREATER',
    'LESS',
    'GREATER_EQUAL',
    'LESS_EQUAL',

 ] + list(reserved.values())

t_COLON = r':'
t_LPAREN = r'\('
t_RPAREN = r'\)'
t_COMMA = r','
t_EQUALS = r'=='
t_NOT_EQUALS = r'!='
t_GREATER_EQUAL = r'>='
t_LESS_EQUAL = r'<='
t_GREATER = r'>'
t_LESS = r'<'
t_ignore = ' \t'

def t_ID(t):
    r'[a-zA-Z_][a-zA-Z0-9_]*'
    t.type = reserved.get(t.value, 'ID')
    return t

def t_STRING(t):
    r'\"([^\\\n]|(\\.))*?\"'
    t.value = t.value[1:-1]
    return t

def t_DATE(t):
    r'\d{2}-\d{2}-\d{4}'

    try:
        t.value = datetime.strptime(t.value, "%d-%m-%Y").date()
        return t

    except ValueError:
        print(f"Data inválida: {t.value}")


def t_TIME(t):
    r'\d{2}:\d{2}'
    hora, minuto = map(int, t.value.split(':'))

    if hora > 23 or minuto > 59:
        print(f"Hora inválida: {t.value}")

    return t

def t_NUMBER(t):
    r'\d+'
    t.value = int(t.value)
    return t

def t_NEWLINE(t):
    r'\n+'
    t.lexer.lineno += len(t.value)
    return t

def t_error(t):
    print(f"Caractere ilegal: {t.value[0]}")
    t.lexer.skip(1)

lexer = lex.lex()