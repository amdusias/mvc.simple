<?php

namespace app\core;

/**
 * Class Application
 */
class Application extends ApplicationBase
{
    public static string $rootDir;
    public static array $config;
    public string $layout = 'main';

    public static Application $app;
    public ?Controller $controller = null;
    public ?UserModel $user;
    public int $userId;

    /**
     * Constructor
     * @param string $rootPath
     * @param array $config
     */
    public function __construct(string $rootPath, array $config)
    {
        $this->init($config['components']);
        $components = $this->sortComponents();

        $this->user = null;

        self::$rootDir = $rootPath;
        self::$config = $config;
        self::$app = $this;

        foreach ($components ?: [] as $value)
        {
            $this->{$value} = $this->getComponent($value);
        }

        $this->userId = $userId = Application::$app->session->get('user');

        if ($userId) {
            $key = $this->userClass::primaryKey();

            $user = Application::$app->cache->get('user');
            $this->user = $this->userClass::findOne([$key => $userId]);
            if (!$user) {
                Application::$app->cache->set('user', $this->user, 3600 * 24);
            }
        }
    }

    /**
     * Checking fo guest
     * @return bool
     */
    public static function isGuest(): bool
    {
        return !self::$app->user;
    }

    /**
     * Login
     * @param UserModel $user
     * @return bool
     */
    public function login(UserModel $user): bool
    {
        $this->user = $user;
        $className = $user::class;
        $primaryKey = $className::primaryKey();
        $value = $user->{$primaryKey};
        Application::$app->session->set('user', $value);

        return true;
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->user = null;
        Application::$app->session->setFlash('success', 'Вы успешно вышли из аккаунта');
        self::$app->session->remove('user');
    }

    /**
     * Run application
     */
    public function run(Application $app)
    {
        try {
            require_once Application::$rootDir . '/route/app.php';

            echo $this->router->resolve();
        } catch (\Exception $e) {
            echo $this->router->renderView('_error', [
                'exception' => $e,
                'title' => $e->getCode()
            ]);
        }
    }
}