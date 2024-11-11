{!! "<?php" !!}

namespace {{ $namespace }};

@if ($extendsCustom)
use  App\Document\Areabrick\{{ $extends }};
@else
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
@endif

class {{ $class }} extends {{ $extends ?? 'AbstractTemplateAreabrick' }}
{!! '{' !!}

    public function getName(): string
    {{ '{' }}
        return '{{ $name }}';
    {{ '}' }}

{!! '}' !!}
