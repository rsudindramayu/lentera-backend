<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//API
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function (RouteCollection $routes) {
    $routes->group('', ['filter' => 'api-key'], static function (RouteCollection $routes) {
        $routes->post('auth/signin', 'AuthController::signIn');
        $routes->post('auth/refresh-token', 'AuthController::refreshToken');
    });

    $routes->group('', ['filter' => 'jwt-auth'], static function (RouteCollection $routes) {
        $routes->get('auth/me', 'AuthController::fetchUser');
        $routes->post('auth/signout', 'AuthController::signOut');

        //setting
        $routes->group('setting', static function (RouteCollection $routes) {
            //features
            $routes->get('features', 'FeatureController::getFeatures');
            $routes->get('features/with-permissions', 'FeatureController::getFeatureWithPermissions');
            $routes->post('features', 'FeatureController::addFeature');
            $routes->put('features/(:num)', 'FeatureController::updateFeature/$1');

            //modules
            $routes->get('modules', 'ModuleController::getModules');
            $routes->post('modules', 'ModuleController::addModule');
            $routes->put('modules/(:num)', 'ModuleController::updateModule/$1');

            //permissions
            $routes->get('permissions', 'PermissionController::getPermissions');
            $routes->post('permissions', 'PermissionController::addPermission');
            $routes->put('permissions/(:num)', 'PermissionController::updatePermission/$1');

            //pengguna
            $routes->get('pengguna', 'PenggunaController::getPengguna');

            //roles
            $routes->get('roles', 'RoleController::getRoles');
            $routes->post('roles', 'RoleController::createRole');
            $routes->put('roles/(:num)', 'RoleController::updateRole/$1');
            $routes->delete('roles/(:num)', 'RoleController::deleteRole/$1');

            //role permissions
            $routes->get('role-permissions/(:num)', 'RolePermissionController::getPermissionByRoleId/$1');
            $routes->post('role-permissions', 'RolePermissionController::syncRolePermissions');

            //user role
            $routes->get('user-roles', 'UserRoleController::getRoleByUserId');
            $routes->post('user-roles', 'UserRoleController::syncUserRoles');
        });
    });

    // Modular Feature Routes
    foreach (glob(APPPATH . 'Modules/*/Config/Routes.php') as $routeFile) {
        require $routeFile;
    }
});
