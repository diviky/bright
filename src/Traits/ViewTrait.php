<?php

namespace Karla\Traits;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

trait ViewTrait
{
    public function ajax($url = null, $params = [], $method = 'post')
    {
        $form         = [];
        $form['pjax'] = $this->get('request')->pjax();
        $form['ajax'] = $form['pjax'] ? false : $this->get('request')->ajax();

        if (!$form['ajax']) {
            $inputs = '';
            foreach ($params as $k => $v) {
                $inputs .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
            }

            $class           = 'render-'.Str::slug($url);
            $params['class'] = '.'.$class;

            $action = '/' !== substr($url, 0, 1) ? $this->route($url) : url($url);

            $start          = '<form role="krender" class="'.$class.'" method="'.$method.'" action="'.$action.'">';
            $form['start']  = $start.$inputs.csrf_field();
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
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                View::share($k, $v);
            }
        } else {
            View::share($key, $value);
        }

        return $this;
    }
}
