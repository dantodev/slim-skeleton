<?php namespace App\Factory;

use Dtkahl\SimpleConfig\Config;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class TwigFactory
{

    public function __invoke(Config $config, Router $router) {
        $twig = new Twig($config->get("app.twig.path"), [
            "cache" => getenv("DEBUG") ? false : $config->get("app.twig.cache")
        ]);



        $twig->addExtension(new class extends \Twig_Extension {
            private $manifest = [];
            public function __construct()
            {
                $manifest_file = APP_ROOT . "/public/build/manifest.json";
                if (file_exists($manifest_file)) {
                    $this->manifest = json_decode(file_get_contents($manifest_file), true);
                }
            }
            public function getName()
            {
                return "skeleton";
            }
            public function getFunctions()
            {
                return [new \Twig_SimpleFunction("asset", array($this, "asset"))];
            }
            public function asset($file_name)
            {
                if (!getenv("DEBUG") && array_key_exists($file_name, $this->manifest)) {
                    $file_name = $this->manifest[$file_name];
                }
                return "/build/" . $file_name;
            }
        });
        $twig->addExtension(new TwigExtension($router, ""));
        $twig->offsetSet("debug", getenv("DEBUG"));

        return $twig;
    }

}
