{!! '<?php' !!}

{{ 'namespace ' . $namespace . ';'}}

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class {{ $class }} extends FrontendController
{{ '{' }}

    public function {{ $action }}Action(Request $request): Response
    {{ '{' }}
        return $this->render('{{ $view }}', []);
    {{ '}' }}

{{ '}' }}
