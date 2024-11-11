{% set input = pimcore_input('input') %}

{!! "{% block content %}" !!}
    {% if editmode %}
        <div class="">
            {!! "{{ input|raw }}" !!}
        </div>
    {% endif %}
    <div class="container">
        {!! "{# Your content here! #}" !!}
    </div>

{!! "{% endblock %}" !!}
