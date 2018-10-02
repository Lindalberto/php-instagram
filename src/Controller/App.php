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
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    /**
     * Rest constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view      = $container->get('renderer');

        $this->clientId     = $this->settings['client_id'];
        $this->clientSecret = $this->settings['client_secret'];
        $this->redirectUri  = $this->settings['redirect'];
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
        $warning = false;
        if (!getenv('CLIENT_ID')) {
            $warning = 'CLIENT_ID não setado.';
        }
        if (!getenv('CLIENT_SECRET')) {
            $warning .= '<br>CLIENT_SECRET não setado.';
        }
        if (!getenv('ADDRESS')) {
            $warning .= '<br>Seu endereço local realmente é \'<strong>http://localhost</strong>\'? Caso não seja, favor passar um endereço diferente através da variável de ambiente <strong>ADDRESS</strong>';
        }

        $error = false;
        if (isset($_SESSION['msg'])) {
            $error = $_SESSION['msg'];
            unset($_SESSION['msg']);
        }

        return $this->view->render($response, 'index.phtml', ['warning' => $warning, 'error' => $error]);
    }

    public function request(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $response
            ->withStatus(302)
            ->withHeader('Location', 
                'https://api.instagram.com/oauth/authorize/?client_id='. $this->clientId .'&redirect_uri='. $this->redirectUri . '/response&response_type=code&scope=basic+public_content+comments+relationships+likes+follower_list'
            );
    }

    public function response(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        unset($_SESSION['code']);
        $code = null;
        if (isset($_GET['code']) && $_GET['code']) {
            $code = $_GET['code'];
        }

        if (!$code) {
            $_SESSION['msg'] = 'Code inválido.';
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        $ch = curl_init('https://api.instagram.com/oauth/access_token');
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectUri .'/response',
            'code'          => $code
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        if (!$return) {
            $_SESSION['msg'] = 'Houve uma falha na requisição ao instagram.';
            return $response->withStatus(302)->withHeader('Location', '/');
        }

        $return = json_decode($return);
        $_SESSION['code'] = $return->access_token;

        /*
        var_dump($return);
        echo "<hr>";
        var_dump($return->user->id);
        echo "<br>";
        var_dump($return->user->username);
        echo "<br>";
        var_dump($return->user->profile_picture);
        echo "<br>";
        var_dump($return->user->full_name);
        echo "<br>";
        var_dump($return->user->bio);
        echo "<br>";
        var_dump($return->user->website);
        echo "<br>";
        var_dump($return->user->is_business);
        */
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