<?php
$url_request = $_SERVER['REQUEST_URI'];
define('APPLICATION_PATH', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
define('APPLICATION_URL', 'http://' . $_SERVER['HTTP_HOST']);

$url = '/' . substr_replace($url_request, '', 0, strlen(APPLICATION_PATH));


set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/../'));
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/../library'));
require_once 'MRCartas/Autoloader.php';
require_once 'App/JogoApp.php';
require_once 'App/Model/Jogo.php';
require_once 'App/Model/Jogador.php';


$jogoApp = new \App\JogoApp();

require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Json/Encoder.php';
$session = new Zend_Session_Namespace();
if (isset($session->jogo) === false) {
    $session->jogo = new \App\Model\Jogo();
}


$jogoApp->get('/', function () use($session) {
            require '../App/View/Home.php';
        });

$jogoApp->get('/oi', function () {
            echo 'helloooo mano!';
        });

$jogoApp->get('/pegar-carta', function () use($session) {
            $carta = $session->jogo->getCarta();
            $jogador = $session->jogo->getJogador();

            $session->jogo->addCartaDescarte($carta);
            $mensagem = "Você acertou! =D";
            if ($carta->isPar() && $jogador->getEscolha() == 'par') {
                $session->jogo->obterDescarte();
            } else if ($carta->isImpar() && $jogador->getEscolha() == 'impar') {
                $session->jogo->obterDescarte();
            } else {
                $mensagem = "Você errou! =(";
            }

            $session->jogo->trocarTurno();

            echo Zend_Json_Encoder::encode(array('naipe' => $carta->getNaipe(),
                'valor' => $carta->getValor(),
                'mensagem' => $mensagem));
        });

$jogoApp->get('/get-placar', function () use($session) {
            $jogador = $session->jogo->getJogador();
            $jogador1 = $session->jogo->getJogador1();
            $jogador2 = $session->jogo->getJogador2();
            $baralho = $session->jogo->getBaralho()->count();
            $descarte = $session->jogo->getDescarte()->count();
            echo Zend_Json_Encoder::encode(array(
                'countBaralho' => $baralho,
                'countDescarte' => $descarte,
                'idJogadorAtual' => $jogador->getId(),
                'numCartas1' => $jogador1->count(),
                'numCartas2' => $jogador2->count()));
        });

$jogoApp->get('/novo-jogo', function () use($session) {
            $session->jogo = null;
            $session->jogo = new \App\Model\Jogo();
        });

$jogoApp->get('/par', function () use($session) {
            $session->jogo->escolher('par');
        });

$jogoApp->get('/impar', function () use($session) {
            $session->jogo->escolher('impar');
        });

$jogoApp->get('/get-jogador-atual', function () use($session) {
            $jogador = $session->jogo->getJogador();
            echo Zend_Json_Encoder::encode(array(
                'idJogadorAtual' => $jogador->getId(),
                'numCartas' => $jogador->count()));
        });

$jogoApp->run($url);
