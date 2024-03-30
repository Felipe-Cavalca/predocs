# Erro: Método Não Permitido (Method Not Allowed)

O erro "Método Não Permitido" (Method Not Allowed) ocorre quando um cliente tenta fazer uma solicitação HTTP utilizando um método (como GET, POST, PUT, DELETE, etc.) que não é permitido para o recurso solicitado.

## Causas Comuns

As causas mais comuns desse erro incluem:

1. **Método Incompatível**: O cliente está usando um método HTTP que não é suportado para o recurso. Por exemplo, tentar atualizar um recurso com o método GET.

## Como Tratar o Erro

Ao lidar com o erro "Método Não Permitido", você pode considerar o seguinte:

1. **Verificar o Método**: Certifique-se de que o método HTTP utilizado na solicitação seja compatível com as operações permitidas para o recurso. Consulte a documentação da API para obter informações sobre quais métodos são suportados para cada endpoint.

## Como Evitar o Erro

Para evitar o erro "Método Não Permitido", certifique-se de que seu cliente está usando o método HTTP correto para a operação desejada. Consulte a documentação da sua API para obter informações detalhadas sobre os métodos permitidos em cada endpoint.

---
* [Erros](/docs/errors/index.md)
* [index](/docs/index.md)
