<?php

$get = curl_init();
curl_setopt($get, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($get, CURLOPT_URL, 'http://www.nfe.fazenda.gov.br/portal/disponibilidade.aspx');
$html = curl_exec($get);
curl_close($get);

$dom = new DOMDocument;
$dom->loadHTML($html);

$response = array('ultimaVerificacao' => '','webservices' => array());

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
//return response
header('Content-type: application/json; charset=utf-8');
echo json_encode($response);

?>

