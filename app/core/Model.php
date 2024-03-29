<?php

namespace app\core;

use app\core\exception\InvalidCsrfTokenException;

/**
 * Class Model
 */
class Model
{
    const RULE_REQUIRED = 'required';
    const RULE_EMAIL = 'email';
    const RULE_MIN = 'min';
    const RULE_MAX = 'max';
    const RULE_MATCH = 'match';
    const RULE_UNIQUE = 'unique';
    const RULE_CSRF = 'csrf';
    const RULE_CAPTCHA = 'captcha';

    public string $token = '';
    public string $captcha = '';

    /**
     * Errors
     * @var array
     */
    public array $errors = [];

    /**
     * Loading data that came from the user
     * @param $data
     */
    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Return array attributes model
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Return array labels model
     * @return array
     */
    public function labels(): array
    {
        return [
            'token' => 'Csrf токен',
            'captcha' => 'Проверка на робота'
        ];
    }

    /**
     * Return label
     * @param $attribute
     * @param bool $label
     * @return string
     */
    public function getLabel($attribute, bool $label = true): string
    {
        $labels = $this->labels();

        if ($label) {
            return $labels[$attribute];
        }

        return '';
    }

    /**
     * Return rules model
     * @return array
     */
    public function rules(): array
    {
        return [
            'token' => [
                self::RULE_CSRF
            ]
        ];
    }

    /**
     * Return messages validate
     * @return array
     */
    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => 'Поле {field} обязательно к заполнению',
            self::RULE_EMAIL => 'Поле {field} должно иметь валидный email адрес',
            self::RULE_MIN => 'Минимальное количество символов в поле {field} должно быть {min}',
            self::RULE_MAX => 'Максимальное количество символов в поле {field} должно быть {max}',
            self::RULE_MATCH => 'Поле {field} должно совпадать со значением поля {match}',
            self::RULE_UNIQUE => 'Запись с этим {field} уже существует',
            self::RULE_CAPTCHA => 'Не пройдена проверка на робота'
        ];
    }

    /**
     * Return message rule
     * @param $rule
     * @return string
     */
    public function errorMessage($rule): string
    {
        return $this->errorMessages()[$rule];
    }

    /**
     * Add error rule
     * @param string $attribute
     * @param string $rule
     * @param array $params
     */
    protected function addErrorByRule(string $attribute, string $rule, array $params = [])
    {
        $params['field'] ??= $this->getLabel($attribute);
        $errorMessage = $this->errorMessage($rule);
        foreach ($params as $key => $value) {
            $errorMessage = str_replace("{{$key}}", $value, $errorMessage);
        }
        $this->errors[$attribute][] = $errorMessage;
    }


    /**
     * Add error rule
     * @param string $attribute
     * @param string $message
     */
    public function addError(string $attribute, string $message)
    {
        $this->errors[$attribute][] = $message;
    }

    /**
     * Validate model
     * @return bool
     * @throws InvalidCsrfTokenException
     */
    public function validate(): bool
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};

            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (!is_string($ruleName)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addErrorByRule($attribute, self::RULE_REQUIRED);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorByRule($attribute, self::RULE_EMAIL);
                }
                if ($ruleName === self::RULE_MIN && mb_strlen($value) < $rule['min']) {
                    $this->addErrorByRule($attribute, self::RULE_MIN, $rule);
                }
                if ($ruleName === self::RULE_MAX && mb_strlen($value) > $rule['max']) {
                    $this->addErrorByRule($attribute, self::RULE_MAX, $rule);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $this->addErrorByRule($attribute, self::RULE_MATCH, $rule);
                }
                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $db = Application::$app->db;
                    $record = $db->getRow("SELECT * FROM `{$tableName}` WHERE `{$uniqueAttr}` = :$uniqueAttr", ["$uniqueAttr" => $value]);
                    if ($record) {
                        $this->addErrorByRule($attribute, self::RULE_UNIQUE);
                    }
                }

                if ($ruleName == self::RULE_CSRF) {
                    Application::$app->csrf->check($value);
                }

                if ($ruleName == self::RULE_CAPTCHA) {
                    if (!Application::$app->captcha->check($value)) {
                        $this->addErrorByRule($attribute, self::RULE_CAPTCHA);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Isset error rule validate
     * @param string $attribute
     * @return mixed
     */
    public function hasError(string $attribute): mixed
    {
        return $this->errors[$attribute] ?? false;
    }

    /**
     * Current error rule validate
     * @param string $attribute
     * @return mixed
     */
    public function getFirstError(string $attribute): mixed
    {
        return $this->errors[$attribute][0] ?? false;
    }
}