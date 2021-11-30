<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

trait ViewTrait
{
    /**
     * set the required data for ajax request.
     *
     * @param null|array|string $url
     * @param null|array        $params
     * @param null|string       $method
     * @param null|array        $attributes
     */
    public function ajax($url = null, $params = [], $method = 'post', $attributes = []): self
    {
        $form = [];
        $form['pjax'] = $this->get('request')->pjax();
        $form['pjax'] = $form['pjax'] ?: $this->get('request')->input('pjax');
        $form['ajax'] = $form['pjax'] ? false : $this->get('request')->ajax();

        if (!$form['ajax']) {
            $parameters = [];
            if (is_array($url)) {
                $parameters = $url[1] ?? [];
                $url = $url[0];
            }

            $action = $url;

            if (is_string($url)) {
                if ('/' !== \substr($url, 0, 1)) {
                    $action = route($url, $parameters);
                } else {
                    $url = str_replace(['"', "'"], ['%22', '%27'], $url);
                    $action = url($url, $parameters);
                }
            }

            if (!\is_array($params)) {
                $params = [];
            }

            $inputs = '';
            foreach ($params as $k => $v) {
                $inputs .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
            }

            $class = '';
            if (is_string($url)) {
                $class = 'render-' . Str::slug($url);
                $params['class'] = '.' . $class;
            }

            if (!\is_array($attributes)) {
                $attributes = [];
            }

            $attribs = [];
            $attribs['role'] = 'krender';
            $attribs['class'] = $class;
            $attribs['name'] = $class;
            $attribs['method'] = $method ?: 'POST';
            $attribs['action'] = $action;

            $start = '<form ' . $this->toAttribs(array_merge($attribs, $attributes)) . '>';
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
