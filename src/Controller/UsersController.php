<?php
/**
 * Users controller.
 *
 * @link http://epi.uj.edu.pl/~13_zieba/silex_test/users
 * @author marta.zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\UsersModel;
use Form\UserForm;

/**
 * Class UsersController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class UsersController implements ControllerProviderInterface
{
    /**
     * Data for view.
     *
     * @access protected
     * @var array $view
     */
    protected $view = array();

    /**
     * Routing settings.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @return UsersController Result
     */
    public function connect(Application $app)
    {
        $usersController = $app['controllers_factory'];
        $usersController->match('/register', array($this, 'registerAction'))
            ->bind('users_register');
        $usersController->match('/register/', array($this, 'registerAction'));
        $usersController->match('/address', array($this, 'addAddressAction'))
            ->bind('add_address');
        $usersController->match('/address/', array($this, 'addAddressAction'));
        $usersController->match('/addmore', array($this, 'addUserDetailsAction'))
            ->bind('add_more');
        $usersController->match('/addmore/', array($this, 'addUserDetailsAction'));
        $usersController->get('/edit', array($this, 'indexAction'));
        $usersController->get('/edit/', array($this, 'indexAction'));
        $usersController->match('/edit/{id}', array($this, 'editDetailsAction'))
            ->bind('users_edit');
        $usersController->match('/edit/{id}/', array($this, 'editDetailsAction'));
        $usersController->match('/editaddress/{id}', array($this, 'editAddressAction'))
            ->bind('edit_address');
        $usersController->match('/editaddress/{id}/', array($this, 'editAddressAction'));
        $usersController->get('/delete', array($this, 'indexAction'));
        $usersController->get('/delete/', array($this, 'indexAction'));
        $usersController->match('/delete/{id}', array($this, 'deleteAction'))
            ->bind('users_delete');
        $usersController->match('/delete/{id}/', array($this, 'deleteAction'));
        $usersController->get('/view', array($this, 'indexAction'));
        $usersController->get('/view/', array($this, 'indexAction'));
        $usersController->get('/view/{id}', array($this, 'viewAction'))
            ->bind('users_view');
        $usersController->get('/view/{id}/', array($this, 'viewAction'));
        $usersController->get('/indexprofile', array($this, 'indexProfileAction'));
        $usersController->get('/indexprofile/', array($this, 'indexProfileAction'))
            ->bind('users_indexprofile');
        $usersController->get('/index', array($this, 'indexAction'));
        $usersController->get('/', array($this, 'indexAction'));
        $usersController->get('/index/', array($this, 'indexAction'));
        $usersController->get('/{page}', array($this, 'indexAction'))
                         ->value('page', 1)->bind('users_index');
        return $usersController;
    }
   /**
     * Index action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function indexAction(Application $app, Request $request)
    {
        try {
            $pageLimit = 7;
            $page = (int) $request->get('page', 1);
            $usersModel = new UsersModel($app);
            $this->view = array_merge(
                $this->view,
                $usersModel->getPaginatedUsers($page, $pageLimit)
            );
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Users not found'));
        }
        return $app['twig']->render('users/index.twig', $this->view);
    }
    
    /**
     * Index profile action.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Output
     */
    public function indexProfileAction(Application $app, Request $request)
    {
        try {
            $id = (int)$request->get('id', null);
            $token = $app['security']->getToken();
            if (null !== $token) {
                $user = $token->getUser()->getUsername();
            }
            $usersModel = new UsersModel($app);
            $userdata = $usersModel->getUserByLogin($user);
            $details = $usersModel->getUserDetailsByHisId($userdata['id']);
            $address = $usersModel->getUserAddressByDetailsId($details['id']);
            $this->view['address'] = $address;
            $this->view['details'] = $details;
            $this->view['id'] = $id;
            $this->view['user'] = $userdata;
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Profile not found.'));
        }
        return $app['twig']->render('users/indexprofile.twig', $this->view);
    }
    
    /**
     * View action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function viewAction(Application $app, Request $request)
    {
        try {
            $id = (int)$request->get('id', null);
            $token = $app['security']->getToken();
            if (null !== $token) {
                $username = $token->getUser()->getUsername();
            }
            $usersModel = new UsersModel($app);
            $userData = $usersModel->getUserByLogin($username);
            $user_id = $userData['id'];
            $this->view['current'] = $user_id;
            $this->view['id'] = $id;
            $this->view['user'] = $usersModel->getUser($user_id);
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('User not found.'));
        }
        return $app['twig']->render('users/view.twig', $this->view);
    }
    
    /**
     * Register action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
     
    public function registerAction(Application $app, Request $request)
    {
        try {
            $data = array();
            $form = $app['form.factory']
                ->createBuilder(new UserForm($app), $data)->getForm();
            $form->remove('id');
            $form->remove('role_id');
            $form->remove('address_id');
            $form->remove('detail_id');
            $form->remove('user_id');
            $form->remove('firstName');
            $form->remove('surname');
            $form->remove('email');
            $form->remove('phone');
            $form->remove('city');
            $form->remove('street');
            $form->remove('number');
            $form->remove('post');
          
            $form->handleRequest($request);
         
            if ($form->isValid()) {
                $data = $form->getData();
                $data['role_id'] = 2;
                $data['password'] = $app['security.encoder.digest']
                   ->encodePassword($data['password'], '');
                $usersModel = new UsersModel($app);
                $usersModel->saveUser($data);
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                    'type' => 'success',
                    'content' => $app['translator']
						->trans('New user added. Congratulations! Please log in to continue.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('add_more'),
                    301
                );
            }
            $this->view['form'] = $form->createView();
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Page not found.'));
        }
        return $app['twig']->render('users/register.twig', $this->view);
    }
    /**
     * Add User Details action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function addUserDetailsAction(Application $app, Request $request)
    {
        try {
            $details = array();
            $token = $app['security']->getToken();
            if (null !== $token) {
                $username = $token->getUser()->getUsername();
            }
            $usersModel = new UsersModel($app);
            $userdata = $usersModel->getUserByLogin($username);
            $details['user_id'] = $userdata['id'];
            $this->view['user'] = $userdata;
            
            if (count($userdata)) {
                $form = $app['form.factory']
                    ->createBuilder(new UserForm($app), $details)->getForm();
                $form->remove('detail_id');
                $form->remove('id');
                $form->remove('role_id');
                $form->remove('user_id');
                $form->remove('login');
                $form->remove('password');
                $form->remove('address_id');
                $form->remove('city');
                $form->remove('street');
                $form->remove('number');
                $form->remove('post');
                $form->handleRequest($request);
             
                if ($form->isValid()) {
                    $details = $form->getData();
                    $usersModel = new UsersModel($app);
                    $usersModel->saveUserDetails($details);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success', 'content'
                                => $app['translator']->trans('New details added.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('users_indexprofile'),
                        301
                    );

                }
                $this->view['form'] = $form->createView();
            } else {
                return $app->redirect(
                    $app['url_generator']->generate('users_addmore'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('users/addmore.twig', $this->view);
    }
    /**
     * Add Address action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function addAddressAction(Application $app, Request $request)
    {
        try {
            $address = array();
            $token = $app['security']->getToken();
            if (null !== $token) {
                $username = $token->getUser()->getUsername();
            }
            
            $usersModel = new UsersModel($app);
            $userdata = $usersModel->getUserByLogin($username);
            $detailsid = $usersModel->getDetailsId($userdata['id']);
            $address['userDetail_id'] = $detailsid;
            $this->view['user'] = $userdata;
            
            if (count($userdata)) {
                $form = $app['form.factory']
                    ->createBuilder(new UserForm($app), $address)->getForm();
                $form->remove('detail_id');
                $form->remove('id');
                $form->remove('role_id');
                $form->remove('login');
                $form->remove('password');
                $form->remove('address_id');
                $form->remove('user_id');
                $form->remove('firstName');
                $form->remove('surname');
                $form->remove('email');
                $form->remove('phone');
                $form->handleRequest($request);
             
                if ($form->isValid()) {
                    $address = $form->getData();
                    $usersModel = new UsersModel($app);
                    $addressid = $usersModel->saveAddress($address);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success', 'content'
                                => $app['translator']->trans('Address added.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('users_indexprofile'),
                        301
                    );
                    return $addressid;
                }
                $this->view['form'] = $form->createView();
            } else {
                return $app->redirect(
                    $app['url_generator']->generate('add_address'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('users/address.twig', $this->view);
    }
    /**
     * Edit details action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function editDetailsAction(Application $app, Request $request)
    {
        try {
            $token = $app['security']->getToken();
            if (null !== $token) {
                $username = $token->getUser()->getUsername();
            }
            $data = array();
            $usersModel = new UsersModel($app);
            $user_id = $usersModel->getUserId($username);
            $user = $usersModel->getUserDetailsByHisId($user_id);
            $this->view['user'] = $user;
            $this->view['id'] = $user_id;
            $data['user_id'] = $user_id;
            
            if (count($user)) {
                $form = $app['form.factory']
                 ->createBuilder(new UserForm($app), $user)->getForm();
                $form->remove('password');
                $form->remove('role_id');
                $form->remove('address_id');
                $form->remove('user_id');
                $form->remove('id');
                $form->remove('detail_id');
                $form->remove('login');
                $form->remove('city');
                $form->remove('street');
                $form->remove('number');
                $form->remove('post');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $usersModel = new UsersModel($app);
                    $usersModel->saveUserDetails($data);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success', 'content'
                                => $app['translator']->trans('User edited.')
                        )
                    );
                    if ($app['security']->isGranted('ROLE_ADMIN')) {
                        return $app->redirect(
                            $app['url_generator']->generate('users_index'),
                            301
                        );
                    } else {
                        return $app->redirect(
                            $app['url_generator']->generate('users_indexprofile'),
                            301
                        );
                    }
                }
                $this->view['form'] = $form->createView();
            } else {
                return $app->redirect(
                    $app['url_generator']->generate('add_more'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error.'));
        }
        return $app['twig']->render('users/edit.twig', $this->view);
    }
    /**
     * Edit address action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function editAddressAction(Application $app, Request $request)
    {
        try {
            $token = $app['security']->getToken();
            if (null !== $token) {
                $username = $token->getUser()->getUsername();
            }
            $data = array();
            $usersModel = new UsersModel($app);
            $user_id = $usersModel->getUserId($username);
            $details_id = $usersModel->getUserDetailsByHisId($user_id);
            $address = $usersModel->getUserAddressByDetailsId($details_id);
            $this->view['user'] = $user_id;
            $this->view['id'] = $user_id;
            $data['detail_id'] = $details_id;
            
            if (count($address)) {
                $form = $app['form.factory']
                 ->createBuilder(new UserForm($app), $address)->getForm();
                $form->remove('password');
                $form->remove('role_id');
                $form->remove('address_id');
                $form->remove('user_id');
                $form->remove('id');
                $form->remove('detail_id');
                $form->remove('login');

                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $usersModel = new UsersModel($app);
                    $usersModel->saveAddress($data);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success', 'content'
                                => $app['translator']->trans('Address edited.')
                        )
                    );
                    if ($app['security']->isGranted('ROLE_ADMIN')) {
                        return $app->redirect(
                            $app['url_generator']->generate('users_index'),
                            301
                        );
                    } else {
                        return $app->redirect(
                            $app['url_generator']->generate('users_indexprofile'),
                            301
                        );
                    }
                }
                $this->view['form'] = $form->createView();
            } else {
                return $app->redirect(
                    $app['url_generator']->generate('add_address'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error.'));
        }
        return $app['twig']->render('users/edit.twig', $this->view);
    }
    /**
     * Delete action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function deleteAction(Application $app, Request $request)
    {
        try {
            $usersModel = new UsersModel($app);
            $id = (int) $request->get('id', 0);
            $user = $usersModel->getUser($id);
            $this->view['user'] = $user;

        if (count($user)) {
            $form = $app['form.factory']
                ->createBuilder(new UserForm($app), $user)->getForm();
            $form->remove('id');
            $form->remove('login');
            $form->remove('password');
            $form->remove('role_id');
            $form->remove('firstName');
            $form->remove('surname');
            $form->remove('address');
            $form->remove('detail_id');
            $form->remove('user_id');
            $form->remove('address_id');
            $form->remove('email');
            $form->remove('phone');
            $form->remove('city');
            $form->remove('street');
            $form->remove('number');
            $form->remove('post');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $usersModel = new UsersModel($app);
                $usersModel->deleteUser($data['id']);
                if ($app['security']->isGranted('ROLE_ADMIN')) {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success',
                            'content' => $app['translator']
                                ->trans('User deleted.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('users_index'),
                        301
                    );
                } else {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success',
                            'content' => $app['translator']
                                ->trans('You have removed your account. Thank you for using our services.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('seances_index'),
                        301
                    );
                }
            }
            $this->view['form'] = $form->createView();
        } else {
            return $app->redirect(
                $app['url_generator']->generate('users_register'),
                301
            );
        }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error.'));
        }
        return $app['twig']->render('users/delete.twig', $this->view);
    }
}
