import ply.yacc as yacc
from lexer import tokens

start = 'comando'

def p_comando(p):
    '''
    comando : reserva
            | cancelamento
            | consulta
            | disponibilidade
            | batch
            | definicao
            | if_statement
    '''
    p[0] = p[1]

def p_reserva(p):
    '''
    reserva : RESERVAR COLON TIPO COLON ID ESPACO COLON STRING DATA COLON DATE HORA_INICIO COLON TIME HORA_FIM COLON TIME
    '''

    tipos_validos = {
    "sala",
    "laboratorio",
    "equipamento",
    "bicicleta",
    "trotinete"
    }

    if p[5] not in tipos_validos:
        raise Exception(f"Tipo inválido: {p[5]}")

    p[0] = {
        "comando": "reservar",
        "tipo": p[5],
        "espaco": p[8],
        "data": p[11],
        "hora_inicio": p[14],
        "hora_fim": p[17]
    }
 
def p_cancelamento(p):
    '''
    cancelamento : CANCELAR COLON RESERVA_ID COLON NUMBER
    '''

    p[0] = {
        "comando": "cancelar",
        "reserva_id": p[5]
    }

def p_consulta(p):
    '''
    consulta : CONSULTA COLON TIPO COLON ID PERIODO COLON DATE FILTRO COLON ID
    '''

    p[0] = {
        "comando": "consulta",
        "tipo": p[5],
        "periodo": p[8],
        "filtro": p[11]
    }

def p_disponibilidade(p):
    '''
    disponibilidade : DISPONIBILIDADE COLON TIPO COLON ID DATA COLON DATE HORA_INICIO COLON TIME HORA_FIM COLON TIME
    '''

    p[0] = {
        "comando": "disponibilidade",
        "tipo": p[5],
        "data": p[8],
        "hora_inicio": p[11],
        "hora_fim": p[14]
    }

def p_batch(p):
    '''
    batch : BATCH COLON lista_comandos
    '''

    p[0] = {
        "comando": "batch",
        "comandos": p[3]
    }

def p_lista_comandos_base(p):
    '''
    lista_comandos : comando_batch comando_batch
    '''
    p[0] = [p[1], p[2]]

def p_lista_comandos_rec(p):
    '''
    lista_comandos : lista_comandos comando_batch
    '''
    p[0] = p[1]
    p[0].append(p[2])

def p_comando_batch(p):
    '''
    comando_batch : reserva
                  | cancelamento
                  | consulta
                  | disponibilidade
    '''
    p[0] = p[1]

def p_definicao(p):
    '''
    definicao : DEF ID
    '''

    p[0] = {
        "comando": "def",
        "nome": p[2]
    }

def p_if(p):
    '''
    if_statement : IF expressao ELSE ID
    '''

    p[0] = {
        "comando": "if",
        "condicao": p[2],
        "else": p[4]
    }

def p_expressao_not(p):
    '''
    expressao : NOT expressao
    '''

    p[0] = {
        "op": "not",
        "expr": p[2]
    }

def p_expressao_comparacao(p):
    '''
    expressao : ID EQUALS ID
              | ID GREATER NUMBER
              | ID LESS NUMBER
              | ID GREATER_EQUAL NUMBER
              | ID LESS_EQUAL NUMBER
    '''

    p[0] = {
        "left": p[1],
        "op": p[2],
        "right": p[3]
    }

def p_expressao_logica(p):
    '''
    expressao : expressao AND expressao
              | expressao OR expressao
    '''

    p[0] = {
        "left": p[1],
        "op": p[2],
        "right": p[3]
    }

def p_error(p):
    if p:
        print(
            f"Erro sintático na linha {p.lineno}: token '{p.value}' inesperado"
        )
    else:
        print("Erro sintático: fim inesperado do input")

parser = yacc.yacc()
