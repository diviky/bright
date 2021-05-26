<?php

declare(strict_types=1);

namespace Diviky\Bright\Traits;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

trait ViewTrait
{
    /**
     * set the required data for ajax request.
     *
     * @param null|string $url
     * @param null|array  $params
     * @param null|string $method
     * @param null|array  $attributes
     */
    public function ajax($url = null, $params = [], $method = 'post', $attributes = []): self
    {
        $form = [];
        $form['pjax'] = $this->get('request')->pjax();
        $form['pjax'] = $form['pjax'] ?: $this->get('request')->input('pjax');
        $form['ajax'] = $form['pjax'] ? false : $this->get('request')->ajax();

        if (!$form['ajax']) {
            $inputs = '';
            if (!\is_array($params)) {
                $params = [];
            }

            foreach ($params as $k => $v) {
                $inputs .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
            }

            $class = 'render-' . Str::slug($url);
            $params['class'] = '.' . $class;

            if (!\is_array($attributes)) {
                $attributes = [];
            }

            $action = '/' !== \substr($url, 0, 1) ? $this->route($url) : url($url);

            $attributes['role'] = 'krender';
            $attributes['class'] = $class;
            $attributes['method'] = $method ?? 'POST';
            $attributes['action'] = $action;

            $start = '<form ' . $this->toAttribs($attributes) . '>';
            $form['start'] = $start . $inputs . csrf_field();
            $form['inputs'] = $inputs;
            $form['end'] = '</form>';
            $form['class'] = $params['class'];
            $form['params'] = $params;
        }

        config(['vajax' => $form]);

        return $this;
    }

    /**
     * Share the variables to view.
     *
     * @param array|string $key
     * @param mixed        $value
     */
    public function share($key, $value = null): self
    {
        if (\is_array($key)) {
            foreach ($key as $k => $v) {
                View::share($k, $v);
            }
        } else {
            View::share($key, $value);
        }

        return $this;
    }

    /**
     * Convert array to html arrtibiutes.
     */
    protected function toAttribs(array $attributes): string
    {
        $output = '';
        foreach ($attributes as $key => $value) {
            $output .= $key . '="' . $value . '" ';
        }

        return $output;
    }
}
