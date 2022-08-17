<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PermissionManagement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreDataSeeder extends Seeder
{

    /**
     * Run the database seeds/
     *
     * @return void
     */
    public function run()
    {

        $ignoredTableList = [
            'oauth_access_tokens',
            'oauth_auth_codes',
            'oauth_clients',
            'oauth_personal_access_clients',
            'oauth_refresh_tokens',
            'migrations'
        ];

        DB::statement("SET foreign_key_checks=0");
        $databaseName = DB::getDatabaseName();
        $tables = DB::select("SELECT * FROM information_schema.tables WHERE table_schema = '$databaseName'");
        foreach ($tables as $table) {
            $name = $table->TABLE_NAME;
            if (!in_array($name, $ignoredTableList)) {
                DB::table($name)->truncate();
            }

        }

        $companies = "INSERT INTO `companies` (`id`, `name`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Sipay Electronics Ltd.', 'company@sipay.com','2015-11-04 10:52:01', '2015-11-04 10:52:01');";

        $userTable[] = [
            'name' => "ssadmin",
            'company_id' => 1,
            'email' => "ssadmin@softgate.com",
            'usergroup_ids'=>"1",
            'password' => app('hash')->make('Nop@ss1234'),
            'created_at' => \Carbon\Carbon::now()
        ];
        $userTable[] = [
            'name' => "SoftGate",
            'company_id' => 1,
            'email' => "admin@sipay.com",
            'usergroup_ids'=>"2",
            'password' => app('hash')->make('123456'),
            'created_at' => format_datetime(now(),'Y-m-d H:i:s')
        ];
        $modules = "INSERT INTO `modules` (`id`, `name`, `icon`, `sequence`, `created_at`, `updated_at`) VALUES
(1001, 'Company', 'fa fa-list-ul', 6, '2015-12-09 22:10:46', '2019-03-21 06:52:50'),
(1002, 'Master Data', 'fa fa-list-ul', 2, '2015-12-09 22:10:46', '2019-03-27 23:03:33'),
(1003, 'Access Control', 'fa fa-list-ul', 3, '2015-12-09 22:10:47', '2016-08-07 01:24:34'),
(1004, 'Configuration', 'fa fa-list-ul', 4, '2015-12-09 22:10:47', '2015-12-09 22:10:47');";


        $submoduleSql = "INSERT INTO `submodules` (`id`, `module_id`, `name`, `controller_name`, `icon`, `sequence`, `created_at`, `updated_at`) VALUES
(2051, 1003, 'Role Management', 'RolesController', 'fa fa-angle-double-right', 4, '2015-12-09 22:10:49', '2015-12-24 00:35:45'),
(2052, 1003, 'User Group Management', 'UsergroupController', 'fa fa-angle-double-right', 5, '2015-12-09 22:10:49', '2015-12-09 22:10:49'),
(2053, 1003, 'Group & Role Association', 'UsergroupRoleController', 'fa fa-angle-double-right', 6, '2015-12-09 22:10:49', '2015-12-09 22:10:49'),
(2054, 1003, 'Role & Page Association', 'RolePageController', 'fa fa-angle-double-right', 7, '2015-12-09 22:10:50', '2015-12-09 22:10:50'),
(2055, 1003, 'User Management', 'UsersController', 'fa fa-angle-double-right', 8, '2015-12-09 22:10:49', '2015-12-24 00:35:45'),

(2071, 1003, 'Forgot Password', 'ForgotPasswordController', 'fa fa-angle-double-right', 9, '2015-12-09 22:10:50', '2015-12-09 22:10:50'),
(2072, 1003, 'Reset Password', 'ResetPasswordController', 'fa fa-angle-double-right', 10, '2015-12-09 22:10:50', '2015-12-09 22:10:50'),

(2073, 1002, 'Module Management', 'ModuleController', 'fa fa-angle-double-right', 1, '2015-12-09 22:10:50', '2015-12-09 22:10:50'),
(2074, 1002, 'Sub Module Management', 'SubmoduleController', 'fa fa-angle-double-right', 2, '2015-12-09 22:10:50', '2015-12-09 22:10:50'),
(2075, 1002, 'Page Management', 'PageController', 'fa fa-angle-double-right', 3, '2015-12-09 22:10:50', '2015-12-09 22:10:50'),
(2076, 1001, 'Company Management', 'CompanyController', 'fa fa-angle-double-right', 11, '2015-12-09 22:10:50', '2015-12-09 22:10:50');";



        $pages = "INSERT INTO `pages` (`id`, `module_id`, `submodule_id`, `name`, `route_name`, `created_at`, `updated_at`) VALUES
(3055, 1003, 2051, 'Role List', 'roles.index',  '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(3056, 1003, 2051, 'Add New Role', 'roles.create',  '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(3057, 1003, 2051, 'Modify Role', 'roles.update',  '2015-12-09 22:12:03', '2015-12-09 22:12:03'),
(3058, 1003, 2051, 'Delete Role', 'roles.destroy',  '2015-12-09 22:12:03', '2015-12-09 22:12:03'),

(3066, 1003, 2052, 'Add New Usergroup', 'user-group.create',  '2015-12-09 22:12:04', '2015-12-09 22:12:04'),
(3067, 1003, 2052, 'Modify Usergroup', 'user-group.update',  '2015-12-09 22:12:04', '2015-12-09 22:12:04'),
(3068, 1003, 2052, 'Delete Usergroup', 'user-group.destroy',  '2015-12-09 22:12:04', '2015-12-09 22:12:04'),
(3069, 1003, 2052, 'Usergroup Index', 'user-group.index',  '2015-12-09 22:12:04', '2015-12-09 22:12:04'),
(3070, 1003, 2052, 'Usergroup View', 'user-group.show', '2015-12-09 22:12:04', '2015-12-09 22:12:04'),

(3075, 1003, 2071, 'Forgot Password', 'forget-password.forgetpassword', '2015-12-09 22:12:05', '2015-12-09 22:12:05'),
(3076, 1003, 2072, 'Reset Password', 'reset-password.resetpassword',  '2015-12-09 22:12:05', '2015-12-09 22:12:05'),


(4001, 1003, 2053, 'Group & Role Association', 'user-group-roles.list', '2015-12-09 22:12:05', '2015-12-09 22:12:05'),
(4002, 1003, 2053, 'Modify Group & Role Association', 'user-group-roles.update',  '2015-12-09 22:12:05', '2015-12-09 22:12:05'),

(4020, 1003, 2054, 'Role & Page Association', 'role-pages.list',  '2015-12-09 22:12:05', '2015-12-09 22:12:05'),
(4021, 1003, 2054, 'Modify Role & Page Association', 'role-pages.update',  '2015-12-09 22:12:05', '2015-12-09 22:12:05'),
(4022, 1003, 2051, 'User List', 'users.index', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4023, 1003, 2051, 'Add New User', 'users.create', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4024, 1003, 2051, 'Update User', 'users.update', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),
(4025, 1003, 2051, 'Delete User', 'users.destroy', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),

(4026, 1002, 2073, 'Module List', 'module.index', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4027, 1002, 2073, 'Add New Module', 'module.create', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4028, 1002, 2073, 'Update Module', 'module.update', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),
(4029, 1002, 2073, 'Delete Module', 'module.destroy', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),

(4030, 1002, 2074, 'Sub Module List', 'submodule.index', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4031, 1002, 2074, 'Add New Sub Module', 'submodule.create', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4032, 1002, 2074, 'Update Sub Module', 'submodule.update', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),
(4033, 1002, 2074, 'Delete Sub Module', 'submodule.destroy', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),

(4034, 1002, 2075, 'Page List', 'page.index', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4035, 1002, 2075, 'Add New Page', 'page.create', '2015-12-09 22:12:02', '2015-12-09 22:12:02'),
(4036, 1002, 2075, 'Update Page', 'page.update', '2015-12-09 22:12:03', '2015-12-09 22:12:03'),
(4037, 1002, 2075, 'Delete Page', 'page.destroy', '2015-12-09 22:12:03', '2015-12-09 22:12:03')";

        $roles = "INSERT INTO `roles` (`id`, `title`, `status`, `company_id`, `created_at`, `updated_at`) VALUES
(1, 'super-super-admin', 1, 1, '2019-03-28 07:37:17', '2019-03-28 01:37:17'),
(2, 'ADMIN_ROLE', 1, 2, '2019-03-28 00:47:11', '2019-03-28 00:47:11');";

        $role_pages = "INSERT INTO `role_pages` (`id`, `role_id`, `page_id`) VALUES
(1, 2, 3055),
(2, 2, 3056),
(3, 2, 3057),
(4, 2, 3058),

(5, 2, 3065),
(6, 2, 3066),
(7, 2, 3067),
(8, 2, 3068),
(9, 2, 3069),
(10, 2, 3070),

(11, 2, 3075),
(12, 2, 3076),

(13, 2, 4001),
(14, 2, 4002),

(15, 2, 4020),
(16, 2, 4021),
(17, 2, 4022),
(18, 2, 4023),
(19, 2, 4024),
(20, 2, 4025),

(21, 1, 4026),
(22, 1, 4027),
(23, 1, 4028),
(24, 1, 4029),

(25, 1, 4030),
(26, 1, 4031),
(27, 1, 4032),
(28, 1, 4033),

(29, 1, 4034),
(30, 1, 4035),
(31, 1, 4036),
(32, 1, 4037);";

        $usergroups = "INSERT INTO `usergroups` (`id`, `name`, `status`, `company_id`, `created_at`, `updated_at`) VALUES
(1, 'super-super-admin-group', 1, 1, '2019-03-22 11:38:12', '2015-11-09 23:17:00'),
(2, 'ADMIN_USERGROUP', 1, 2, '2019-03-28 00:47:11', '2019-03-28 00:47:11');";

        $usergroupRole = "INSERT INTO `usergroup_roles` (`id`, `usergroup_id`, `role_id`) VALUES
(1, 1, 1),
(2, 2, 2);";

        $userUserGroup = "INSERT INTO `user_usergroups` (`id`, `usergroup_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2019-03-28 00:47:11', '2019-03-28 00:47:11'),
(2, 2, 2, '2019-03-30 05:39:26', '2019-03-30 05:39:26');";

        $userTypeSubmodules = "INSERT INTO `user_type_submodules`
    (`id`, `user_type_id`, `submodule_id`) VALUES
(null, 1, 2051),
(null, 1, 2052),
(null, 1, 2053),
(null, 1, 2054),
(null, 1, 2055),
(null, 1, 2071),
(null, 1, 2072),
(null, 1, 2073),
(null, 1, 2074),
(null, 1, 2075),
(null, 1, 2076)";

        DB::insert($companies);
        User::insert($userTable);
        DB::insert($modules);
        DB::insert($submoduleSql);
        DB::insert($pages);
        DB::insert($roles);
        DB::insert($usergroups);
        DB::insert($usergroupRole);
        DB::insert($role_pages);
        DB::insert($userUserGroup);
        DB::insert($userTypeSubmodules);

        (new PermissionManagement())->resetAccessPermission();

    }


}
