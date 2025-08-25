## 一个让 ThinkPHP8 开发体验更好的包

### 特性介绍
- 使用实体类接收POST请求体数据(DTO)
- 便捷的数据验证机制（基于ThinkPHP8）
- 服务层注解注入(#[Inject])

### 安装

```
composer require linron/think-dto:dev-main
```

### 用法
#### 控制器

```
<?php

namespace app\controller;

use app\BaseController;
use app\domain\IdDto;
use app\domain\UserDto;
use app\domain\UserListSearchDto;
use app\service\UserService;
use linron\thinkdto\attributes\Inject;
use linron\thinkdto\attributes\RequestBody;
use linron\thinkdto\middlewares\ThinkMvcMiddleware;
use linron\thinkdto\scenes\Create;
use linron\thinkdto\scenes\Delete;
use linron\thinkdto\scenes\Search;
use linron\thinkdto\scenes\Update;
use think\Collection;
use think\response\Json;

class Index extends BaseController
{
    protected $middleware = [
        ThinkMvcMiddleware::class,
    ];

    #[Inject]
    public UserService $userService;

    /**
     * 创建用户
     * @param UserDto $userDto
     * @return Json
     */
    #[RequestBody(group: Create::class)]
    public function createUser(UserDto $userDto): Json
    {
        return json([
            "code" => 200,
            "msg" => "Ok",
            "data" => $userDto,
        ]);
    }

    /**
     * 更新用户
     * @param UserDto $userDto
     * @return Json
     */
    #[RequestBody(group: Update::class)]
    public function updateUser(UserDto $userDto): Json
    {
        return json([
            "code" => 200,
            "msg" => "Ok",
            "data" => $userDto,
        ]);
    }

    /**
     * 删除用户
     * @param IdDto $dto
     * @return Json
     */
    #[RequestBody(group: Delete::class)]
    public function deleteUser(IdDto $dto): Json
    {
        return json([
            "code" => 200,
            "msg" => "Ok",
            "data" => $dto,
        ]);
    }

    /**
     * 用户列表
     * @param UserListSearchDto $dto
     * @return Json
     */
    #[RequestBody(group: Search::class)]
    public function listUser(UserListSearchDto $dto): Json
    {
        return json([
            "code" => 200,
            "msg" => "Ok",
            "data" => $this->userService->getUserList($dto),
        ]);
    }


    /**
     * 批量创建永固
     * @param Collection $users
     * @return Json
     */
    #[RequestBody(group: Create::class, subType: UserDto::class)]
    public function batchCreateRoles(Collection $users): Json
    {
        return json([
            "code" => 200,
            "msg" => "Ok",
            "data" => $users,
        ]);
    }
}
```

#### UserDto
```
<?php

namespace app\domain;

use linron\thinkdto\attributes\CollectionType;
use linron\thinkdto\attributes\DefaultVal;
use linron\thinkdto\attributes\Valid;
use linron\thinkdto\BaseDto;
use linron\thinkdto\scenes\Create;
use think\Collection;

class UserDto extends BaseDto
{
    #[Valid(name: 'require', message: 'ID必传', excludes: [Create::class])]
    public ?int $id;

    #[Valid(name: 'require', message: '用户名必传')]
    public ?string $name;

    #[Valid(name: 'number', message: '年龄必须是数字')]
    #[DefaultVal(value: 0)]
    public ?int $age;

    #[Valid(name: 'require', message: '请选择角色')]
    #[CollectionType(type: RoleDto::class)]
    public ?Collection $roles;
}
```

#### RoleDto

```
<?php

namespace app\domain;

use linron\thinkdto\attributes\Valid;
use linron\thinkdto\BaseDto;

class RoleDto extends BaseDto
{
    #[Valid(name: 'require', message: '角色名称必传')]
    public ?string $name;

    public ?string $desc;
}
```

#### UserListSearchDto

```
<?php

namespace app\domain;

use linron\thinkdto\BaseDto;

class UserListSearchDto extends BaseDto
{
    public ?string $name;

    public ?int $age;
}
```

#### IdDto

```
<?php

namespace app\domain;

use linron\thinkdto\attributes\Valid;
use linron\thinkdto\BaseDto;

class IdDto extends BaseDto
{
    #[Valid(name: 'require', message: 'ID必传')]
    public ?int $id;
}
```

#### UserService

```
<?php

namespace app\service;

use app\domain\UserListSearchDto;

class UserService
{

    public function getUserList(UserListSearchDto $searchDto): array
    {
        return [
            "code" => 200,
            "msg" => "Ok",
            "data" => [
                "list" => [
                    [
                        "id" => 1,
                        "name" => "lin",
                        "age" => 18,
                    ],
                    [
                        "id" => 2,
                        "name" => "lin",
                        "age" => 18,
                    ]
                ]
            ]
        ];
    }
}
```