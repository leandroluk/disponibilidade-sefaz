<?php

header('content-type: application/json; charset=utf-8');

function is_valid_callback($subject) {
    $identifier_syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

    $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
        'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
        'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
        'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
        'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
        'private', 'public', 'yield', 'interface', 'package', 'protected',
        'static', 'null', 'true', 'false');

    return preg_match($identifier_syntax, $subject) && !in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
}

;

$get = curl_init();
curl_setopt($get, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($get, CURLOPT_URL, 'http://www.nfe.fazenda.gov.br/portal/disponibilidade.aspx');
$html = curl_exec($get);
curl_close($get);
$dom = new DOMDocument;
$dom->loadHTML($html);
$response = array(
    'ultimaVerificacao' => '',
    'webservices' => array()
);
$table = $dom->getElementsByTagName('table')->item(1)->childNodes;
foreach ($table as $tableNode) {
    if ($tableNode->tagName == 'caption') {
        $caption = new DateTime(preg_replace("/\r\n|\r|\n|\t/", '', str_replace('/', '-', substr($tableNode->textContent, -23))));
        $response['ultimaVerificacao'] = $caption->format('Y-m-d H:i:s');
    };
    if ($tableNode->tagName == 'tr' && strpos($tableNode->nodeValue, 'Evento') === false) {
        $tr = $tableNode->childNodes;
        $paramNames = [
            'autorizacao',
            'retornoAutorizacao',
            'inutilizacao',
            'consultaProtocolo',
            'statusServico',
            'tempoMedio',
            'consultaCadastro',
            'recepcaoEvento'
        ];
        $keyName = '';
        $array = [];
        foreach ($tr as $i => $trNode) {
            if ($trNode->nodeName == 'td') {
                if (strlen($trNode->nodeValue) != 0) {
                    if ($i == 0) {
                        //autorizadorutorizador
                        $keyName = $trNode->nodeValue;
                    } else {
                        //tempoMedio
                        $array[$paramNames[$i - 1]] = $trNode->nodeValue;
                    };
                }
                if (strlen($trNode->nodeValue) == 0) {
                    $td = $trNode->childNodes;
                    foreach ($td as $tdNode) {
                        if ($tdNode->tagName == 'img') {
                            foreach ($tdNode->attributes as $attr) {
                                if ($attr->nodeName == 'src') {
                                    $status = null;
                                    if (strpos($attr->nodeValue, 'verde') == true) {
                                        $status = '2';
                                    };
                                    if (strpos($attr->nodeValue, 'amarel') == true) {
                                        $status = '1';
                                    };
                                    if (strpos($attr->nodeValue, 'vermelh') == true) {
                                        $status = '0';
                                    };
                                    $array[$paramNames[$i - 1]] = $status;
                                };
                            };
                        };
                    };
                };
            };
        };
        $response['webservices'][$keyName] = $array;
    };
};
$json = json_encode($response);

# JSON if no callback
if( ! isset($_GET['callback']))
    exit($json);

# JSONP if valid callback
if(is_valid_callback($_GET['callback']))
    exit("{$_GET['callback']}($json)");

# Otherwise, bad request
header('status: 400 Bad Request', true, 400);
?>

