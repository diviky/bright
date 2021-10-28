<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

trait Message
{
    /**
     * @psalm-return array{status: string, code: 200|500, message: array|null|string, data?: mixed}
     *
     * @param mixed  $result
     * @param string $action
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function message($result = true, $action = 'save', $name = 'row'): array
    {
        if ($result) {
            $response = [
                'status' => 'OK',
                'code' => 200,
                'message' => trans(':Name :action successfully', ['name' => $name, 'action' => $action . 'd']),
            ];

            if (!is_bool($result) && !is_string($result)) {
                $response['data'] = $result;
            }

            return $response;
        }

        return [
            'status' => 'ERROR',
            'code' => 500,
            'message' => trans('Unable to :action :name. Please try again.', ['name' => $name, 'action' => $action]),
        ];
    }

    /**
     * Delete message.
     *
     * @param mixed  $result
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function deleted($result = true, $name = 'row')
    {
        return $this->message($result, 'delete', $name);
    }

    /**
     * Insert message.
     *
     * @param mixed  $result
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function inserted($result = true, $name = 'row')
    {
        return $this->message($result, 'save', $name);
    }

    /**
     * Updated message.
     *
     * @param mixed  $result
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function updated($result = true, $name = 'row')
    {
        return $this->message($result, 'update', $name);
    }
}
