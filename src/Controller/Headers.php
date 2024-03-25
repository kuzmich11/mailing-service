<?php

namespace App\Controller;

/**
 * Класс для передачи заголовков ответа
 */
class Headers
{
    /** @var array $headers Массив текущие установленные заголовки */
    protected array $headers;

    /**
     * Конструктор
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        $this->set($headers);
    }

    /**
     * Добавление заголовков
     * Передача массивом: add(['Заголовок' => 'Значение'])
     * Передача строкой:  add(заголовок, значение)
     *
     * @param string|array $headers Заголовки которые необходимо добавить
     * @param string $context Содержание заголовка
     *
     * @return static
     */
    public function add(string|array $headers, string $context = ''): static
    {
        if (!is_array($headers)){
            $headers = [$headers => $context];
        }
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Удаление заголовков
     *
     * @param array|string $headers Заголовки которые необходимо удалить
     * Строка если только один заголовок, массив если несколько
     *
     * @return static
     */
    public function delete(array|string $headers): static
    {
        foreach ((array)$headers as $key){
            unset($this->headers[$key]);
        }
        return $this;
    }

    /**
     * Сброс заголовков
     *
     * @return static
     */
    public function reset(): static
    {
       $this->headers  = [];

       return  $this;
    }

    /**
     * Получение заголовков
     *
     * @return array
     */
    public function get(): array
    {
        return $this->headers;
    }

    /**
     * Установка заголовков
     * @param array $headers
     *
     * @return static
     */
    public function set(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

}