<?php

namespace Diviky\Bright\Traits;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

trait ViewTrait
{
    public function ajax($url = null, $params = [], $method = 'post', $attributes = [])
    {
        $form         = [];
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

            $class           = 'render-' . Str::slug($url);
            $params['class'] = '.' . $class;

            if (!\is_array($attributes)) {
                $attributes = [];
            }

            $action = '/' !== \substr($url, 0, 1) ? $this->route($url) : url($url);

            $attributes['role']   = 'krender';
            $attributes['class']  = $class;
            $attributes['method'] = $method ?? 'POST';
            $attributes['action'] = $action;

            $start          = '<form ' . $this->toAttribs($attributes) . '>';
            $form['start']  = $start . $inputs . csrf_field();
            $form['inputs'] = $inputs;
            $form['end']    = '</form>';
            $form['class']  = $params['class'];
            $form['params'] = $params;
        }

        config(['vajax' => $form]);

        return $this;
    }

    public function share($key, $value = null)
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

    protected function toAttribs($attributes)
    {
        $output = '';
        foreach ($attributes as $key => $value) {
            $output .= $key . '="' . $value . '" ';
        }

        return $output;
    }
}
