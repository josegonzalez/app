<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\Configure;
use Cake\Datasource\Exception\MissingModelException;
use Cake\Event\Event;
use Crud\Controller\ControllerTrait;
use UnexpectedValueException;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    use ControllerTrait;

    /**
     * Instance of the Crud class created during initialization.
     * Won't be set until after Controller::initialize() is called.
     *
     * @var \Crud\Controller\Component\Crud
     * @deprecated 3.1.0 Use viewBuilder() instead.
     */
    public $Crud;

    /**
     * Whether or not to treat a controller as
     * if it were an crud view controller or not.
     *
     * Used to turn CrudView on and off at a class-level
     *
     * @var bool
     */
    protected $isCrudView = false;

    /**
     * A list of actions where the CrudView.View
     * listener should be enabled. If an action is
     * in this list but `isCrudView` is false, the
     * action will still be rendered via CrudView.View
     *
     * @var array
     */
    protected $crudViewActions = [];

    /**
     * A list of actions that should be allowed for
     * authenticated users
     *
     * @var array
     */
    protected $allowedActions = [];

    /**
     * A list of actions where the Crud.SearchListener
     * and Search.PrgComponent should be enabled
     *
     * @var array
     */
    protected $searchActions = ['index', 'lookup'];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog',
                'Crud.RelatedModels',
                'Crud.Redirect',
            ],
        ]);

        if ($this->isCrudView || in_array($this->request->getParam('action'), $this->crudViewActions)) {
            $this->Crud->addListener('CrudView.View');
        }

        if (in_array($this->request->getParam('action'), $this->searchActions) && $this->modelClass !== null) {
            list($plugin, $tableClass) = pluginSplit($this->modelClass);
            try {
                if ($this->$tableClass->behaviors()->hasMethod('filterParams')) {
                    $this->Crud->addListener('Crud.Search');
                    $this->loadComponent('Search.Prg', [
                        'actions' => $this->searchActions,
                    ]);
                }
            } catch (MissingModelException $e) {
            } catch (UnexpectedValueException $e) {
            }
        }

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
    }

    /**
     * Before filter callback.
     *
     * @param \Cake\Event\Event $event The beforeFilter event.
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(Event $event)
    {
        $response = parent::beforeFilter($event);

        $this->Crud->on('beforePaginate', function (Event $event) {
            $repository = $event->getSubject()->query->getRepository();
            $primaryKey = $repository->getPrimaryKey();

            if (!is_array($primaryKey)) {
                $this->paginate['order'] = [
                    sprintf('%s.%s', $repository->getAlias(), $primaryKey) => 'asc'
                ];
            }
        });

        if ($this->Crud->isActionMapped()) {
            $this->Crud->action()->setConfig('scaffold.sidebar_navigation', false);
            $this->Crud->action()->setConfig('scaffold.brand', Configure::read('App.name'));
            $this->Crud->action()->setConfig('scaffold.site_title', Configure::read('App.name'));
            if (method_exists($this, 'getUtilityNavigation')) {
                $this->Crud->action()->setConfig('scaffold.utility_navigation', $this->getUtilityNavigation());
            }
        }

        $isRest = in_array($this->response->getType(), ['application/json', 'application/xml']);
        $isCrudView = $this->isCrudView || in_array($this->request->getParam('action'), $this->crudViewActions);
        if (!$isRest && $isCrudView && empty($this->request->getParam('_ext'))) {
            $this->viewBuilder()->setClassName('CrudView\View\CrudView');
        }

        return $response;
    }

    /**
     * Check if the provided user is authorized for the request.
     *
     * @param array|\ArrayAccess|null $user The user to check the authorization of.
     *   If empty the user fetched from storage will be used.
     * @return bool True if $user is authorized, otherwise false
     */
    public function isAuthorized($user = null)
    {
        $action = $this->request->getParam('action');
        if ($action == 'isAuthorized') {
            throw new MissingActionException([
                'controller' => $this->name . 'Controller',
                'action' => $this->request->getParam('action'),
                'prefix' => $this->request->getParam('prefix') ?: '',
                'plugin' => $this->request->getParam('plugin'),
            ]);
        }

        if (in_array($action, $this->allowedActions)) {
            return true;
        }

        return false;
    }
}
