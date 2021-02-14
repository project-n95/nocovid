<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Database\DatabaseRestore;
use WPStaging\Framework\Database\SearchReplace;

class RestoreDatabaseTask extends AbstractTask
{
    use ResourceTrait;
    //use MaintenanceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.database';
    const REQUEST_DTO_CLASS = RestoreDatabaseDto::class;
    const TASK_NAME = 'snapshot_site_restore_database';
    const TASK_TITLE = 'Importing Database';

    /** @var RestoreDatabaseDto */
    protected $requestDto;

    /** @var DatabaseRestore */
    private $service;

    private $sessionManager;

    private $session;

    private $sessionToken;

    public function __construct(DatabaseRestore $service, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        //$this->skipMaintenanceMode();
        $this->service = $service;
        $this->service->setLogger($this->logger);

        /*$this->sessionToken = wp_get_session_token();
        $this->sessionManager = \WP_Session_Tokens::get_instance(get_current_user_id());
        $this->session = $this->sessionManager->get(wp_get_session_token());*/
    }

    public function __destruct()
    {
        parent::__destruct();
        //$this->enableMaintenance(false);
    }

    public function init()
    {
        //$this->enableMaintenance(true);
    }

    public function execute()
    {
        $this->prepare();
        $this->service->restore();

        $steps = $this->requestDto->getSteps();
        $steps->setCurrent($this->service->getCurrentLine());
        $this->logger->info(sprintf('Executed %d/%d queries', $steps->getCurrent(), $steps->getTotal()));

//        wp_cache_init();
//        wp_cache_flush();
//        $this->sessionManager->destroy($this->sessionToken);
//        $this->sessionManager->create(172800);
//        $user = new \WP_User(get_current_user_id());
//        wp_clear_auth_cookie();
//        wp_set_current_user($user->ID);
//        wp_set_auth_cookie($user->ID);
//        do_action('wp_login', $user->user_login);
//        grant_super_admin(get_current_user_id());
//        $user = new \WP_User(get_current_user_id());
//        $user->add_role('administrator');
//        wp_set_auth_cookie($user->ID);
//        do_action('wp_login', $user->user_login);
        return $this->generateResponse();
    }

    public function prepare()
    {
        parent::prepare();

        $this->service->setShouldStop([$this, 'isThreshold']);
        $this->service->setFile($this->requestDto->getFile());
        $this->service->seekLine($this->requestDto->getSteps()->getCurrent());

        if (!$this->requestDto->getSteps()->getTotal()) {
            $this->requestDto->getSteps()->setTotal($this->service->getTotalLines());
        }

        if (!$this->requestDto->getSearch()) {
            return;
        }

        //'sourceAbspath' => $this->dto->getFileHeaders()->getAbspath(),
        //                'sourceSiteUrl' => $this->dto->getFileHeaders()->setSiteUrl(),


/*        $searchDefault = array(
            '\/\/' . str_replace( '/', '\/', ''), // \/\/host.com or \/\/host.com\/subfolder
            '//' . $this->get_url_without_scheme( $this->options->url ), //host.com or //host.com/subfolder
            rtrim( $this->options->path, DIRECTORY_SEPARATOR ),
            str_replace( '/', '%2F', $this->get_url_without_scheme( $this->options->url ) )
        );

        $replaceDefault = array(
            '\/\/' . str_replace( '/', '\/', $this->getDestinationHost() ), // \/\/host.com or \/\/host.com\/subfolder
            '//' . $this->getDestinationHost(), // //host.com or //host.com/subfolder
            rtrim( ABSPATH, '/' ),
            $helper->get_home_url_without_scheme()
        );*/

        $searchDefault = '//' . $this->requestDto->getSourceAbspath();
        $replaceDefault = '//test.com';

        $search = array_merge($searchDefault, $this->requestDto->getSearch());
        $replace = array_merge($replaceDefault, $this->requestDto->getReplace());

        $searchReplace = (new SearchReplace)
            ->setSearch($search)
            ->setReplace($replace)
        ;

        $this->service->setSearchReplace($searchReplace);
    }

    public function getTaskName()
    {
        return self::TASK_NAME;
    }

    public function getStatusTitle(array $args = [])
    {
        return __(self::TASK_TITLE, 'wp-staging');
    }

    public function getRequestNotation()
    {
        return self::REQUEST_NOTATION;
    }

    public function getRequestDtoClass()
    {
        return self::REQUEST_DTO_CLASS;
    }

}
