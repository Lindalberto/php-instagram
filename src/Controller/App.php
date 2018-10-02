<?php 
namespace app\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Slim\Container;

class App
{
    protected $container;
    protected $view;

    /**
     * Rest constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view      = $container->get('renderer');
    }

    /**
     * getter mágico para acessar o container do slim.
     * @param $name
     * @return mixed
     */
    public function __get($name){
        return $this->container->get($name);
    }

    /*
    public function index(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        try {
            $onRedirect = function(
                $request,
                $response,
                $uri
            ) {
                echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "<br><br>";
            };

            $client = new \GuzzleHttp\Client(['verify' => __DIR__ . '/../../cacert.pem']);
            $res    = $client->request('GET', 'https://api.instagram.com/oauth/authorize', [
                'query' => [
                    'client_id' => '9735d232f3eb4dbc86b09235dd52bfbb', 
                    'redirect_uri' => 'http://localhost/response', 
                    'response_type' => 'code'
                ],
                'allow_redirects' => [
                    'max'             => 10,        // allow at most 10 redirects.
                    'strict'          => true,      // use "strict" RFC compliant redirects.
                    'referer'         => true,      // add a Referer header
                    'protocols'       => ['http', 'https'], // only allow https URLs
                    'on_redirect'     => $onRedirect,
                    'track_redirects' => true
                ]
            ]);

            echo $res->getStatusCode();
            echo "<hr>";
            // 200
            echo $res->getHeaderLine('content-type');
            echo "<hr>";
            // 'application/json; charset=utf8'
            echo $res->getReasonPhrase();
            echo "<hr>";

            $body = $res->getBody();
            echo $body;
            echo "<hr>";

            echo $body->getContents();
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }
    }
    */

    public function index(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->view->render($response, 'index.phtml', $args);
    }

    public function request(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $clientId = $this->settings['client_id'];
        return $response
            ->withStatus(302)
            ->withHeader('Location', 
                'https://api.instagram.com/oauth/authorize/?client_id='. $clientId .'&redirect_uri=http://localhost/response&response_type=code&scope=basic+public_content+comments+relationships+likes+follower_list'
            );
    }

    public function response(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        if (isset($_GET['code']) && $_GET['code']) {
            $_SESSION['code'] = $_GET['code'];
        }
        
        return $response->withStatus(302)->withHeader('Location', '/');
    }

    public function teste(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        try {
            $client = new \GuzzleHttp\Client(['verify' => __DIR__ . '/../../cacert.pem']);
            $res    = $client->request('GET', 'https://api.instagram.com/v1/tags/nofilter/media/recent', [
                'query' => [
                    'access_token' => $_SESSION['code'],
                ],
            ]);

            echo $res->getStatusCode();
            echo "<hr>";
            // 200
            echo $res->getHeaderLine('content-type');
            echo "<hr>";
            // 'application/json; charset=utf8'
            echo $res->getReasonPhrase();
            echo "<hr>";

            $body = $res->getBody();
            echo $body;
            echo "<hr>";

            echo $body->getContents();
        } catch (RequestException $e) {
            echo "DEU ERRO";
            echo "<br>";
            echo Psr7\str($e->getRequest());
            echo "<br>";echo "<br>";
            echo Psr7\str($e->getResponse());
            echo "<br>";echo "<br>";

            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }
    }
}