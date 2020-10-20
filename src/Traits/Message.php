<?php

namespace Diviky\Bright\Traits;

trait Message
{
    public function message($result = true, $action = 'save', $name = 'row', $id = true)
    {
        if ($result) {
            $response = [
                'status'  => 'OK',
                'code'    => 200,
                'message' => __(':Name :action successfully', ['name' => $name, 'action' => $action . 'd']),
            ];

            if ($id && !\is_bool($result)) {
                $response['id'] = $result;
            }

            return $response;
        }

        return [
            'status'  => 'ERROR',
            'code'    => 500,
            'message' => __('Unable to :action :name. Please try again.', ['name' => $name, 'action' => $action]),
        ];
    }

    public function deleted($result = true, $name = 'row')
    {
        return $this->message($result, 'delete', $name, false);
    }

    public function inserted($result = true, $name = 'row')
    {
        return $this->message($result, 'save', $name);
    }

    public function updated($result = true, $name = 'row')
    {
        return $this->message($result, 'update', $name, false);
    }
}
