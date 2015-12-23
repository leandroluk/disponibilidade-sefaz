# wsDisponibilidadeSefaz

A SEFAZ não disponibiliza nenhuma forma para que nós programadores saibamos como estão todos os servidores de emissão de NF-e (sem que seja necessário o uso de um certificado digital). Isso complica na hora de desenvolver aplicações pois nem todas as plataformas fazem a leitura de um certificado ou então nem todo mundo tem um certificado digital "usável" naquele momento.

Partindo desse problema, busquei diversos métodos na internet até chegar nessa solução que usa o PHP para pegar/tratar a página de disponibilidade que está no site da SEFAZ e a partir disso gera um JSONP usável em qualquer plataforma ou linguagem.

### Começando (Get Started!)

Para poder usar o arquivo é necessário qualquer servidor PHP (existem diversos como o [WAMP](http://www.wampserver.com), [XAMPP](https://www.apachefriends.org/), [EasyPHP](http://www.easyphp.org) denre outros). Para "instalar" o serviço do webservice basta jogar este arquivo dentro da pasta raiz (ou outra que você preferir) do seu servidor. Após isso o serviço já estará disponível acessando a url do seu servidor/consultaNFE.php como no exemplo:

```
http://localhost/consultaNFE.php
```

**NOTA:** como a aplicação retorna o mime-type JSONP, não é necessário CORS para acessá-la.
