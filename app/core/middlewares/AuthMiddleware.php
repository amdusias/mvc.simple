<?php

namespace app\core\middlewares;

use app\core\Application,
    app\core\exception\ForbiddenException;

/**
 * Class AuthMiddleware
 */
class AuthMiddleware extends BaseMiddleware
{
    protected array $actions = [];

    /**
     * Constructor
     * @param array $actions
     */
    public function __construct(array $actions = [])
    {
        $this->actions = $actions;
    }

    /**
     * Execute
     * @throws ForbiddenException
     */
    public function execute()
    {
        if (Application::isGuest()) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }
    }
}