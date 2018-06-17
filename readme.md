# 无限分类的正确姿势：
## 第一步：创建一个全新的laravel项目category，并通过git来管理该项目，具体细节就不多说了。
## 第二步：做些准备工作：
### ①、新建一个名为category的数据库，并在.env文件里配置数据库的信息；
### ②、在路由文件web.php里面创建category的相关路由（这里可以用资源路由）
> Route::resource('category','CategoryController');
### ③、通过命令行工具同时创建模型model、控制器controller、和迁移文件migration
> php artisan make:model Category -mcr
### ④、创建视图文件(如果要使用模板需要先执行以下php artisan make:auth)：
> resources/views/categories/index.blade.php
### ⑤、在控制器CategoryController的index方法里面添加如下代码：
>     public function index(){
>          return view('categories.index');
>     }
### ⑥、为了方便访问index页面，在导航栏添加一个新导航：
       <!-- Left Side Of Navbar -->
       <ul class="navbar-nav mr-auto">
           <li class="nav-item">
               <a class="nav-link" href="{{ route('category.index') }}">分类管理</a>
           </li>
       </ul>
## 第三步：核心来了，使用laravel-nestedset来实现无限级分类，更加详细内容请点击[这里](http://pilishen.com/posts/laravel-nestedset-the-proper-way-to-implement-infinite-dynamic-multi-level-nested-categories "霹雳神")  ：
### ①、安装nestedset，运行：
> composer require kalnoy/nestedset
### ②、配置迁移文件：
#### 你可以使用NestedSet类的columns方法来添加有默认名字的字段：
     ...
    use Kalnoy\Nestedset\NestedSet;//这里千万不要忘记了

    Schema::create('categories', function (Blueprint $table) {
              $table->increments('id');
              $table->string('name');
              NestedSet::columns($table);//这里就是添加的地方
              $table->timestamps();
          });
#### 删除字段：
    public function down()
     {
         Schema::dropIfExists('categories');
         NestedSet::dropColumns($table);//这里就是添加的地方
     }
### ③、配置Category模型model：
#### 你的模型需要使用Kalnoy\Nestedset\NodeTraittrait 来实现nested sets：
> use Kalnoy\Nestedset\NodeTrait  //这里就是添加的地方<br>

>  class Foo extends Model {<br>
      use NodeTrait;//这里就是添加的地方<br>
  }
### ④、配置完成了，现在运行如下代码迁移文件创建数据表：
> php artisan migrate
### ⑤、填充数据：(！！注意，不要手工填入，因为产生了lef和right太麻烦了)
#### 执行如下命令，创建数据工厂：
> php artisan make:factory --model=Category Category
#### 编辑工厂文件factories/category.php如下:
> return ['name' => $faker->name];
#### 在命令行输入如下代码，填充数据：
> php artisan tinker

> factory('App\Category',3)->create()

## 第四步：这里需要引入vue，因为需要用到它的一些插件来循环输出分类列表，但是vue天生就已经是配置好了的，所以直接使用就行了。
### 但是在使用之前需要安装设置npm的淘宝镜像，执行如下代码：
> npm config set registry https://registry.npm.taobao.org  //设置了镜像才能快速的安装

> npm install

> npm run dev //新增或修改了vue代码后必须进行编译才能生效。
### 1、列出所有根分类：
#### ①、在resources/assets/js/components目录下创建categories/CategoryComponent.vue文件，内容如下：
    <template>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card card-default">
                        <div class="card-header">添加根分类</div>
                        <div class="card-body">
                            <input class="form-control" type="text">
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-default">
                        <div class="card-header">分类列表</div>
    
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item" v-for="category in categories">{{ category.name }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    
    <script>
        export default {
            mounted() {
                this.getCategories();
            },
            data() {
                return {
                    categories:[]
                }
            },
            methods: {
                getCategories() {  //获取数据库里面的所有根分类
                    axios.get('category/getCategories').then(res=> { //这里不要忘记了到路由里面添加该路由
                        this.categories = res.data;
                    }).catch(error=> {
                        throw error
                    })
                }
            }
        }
    </script>
#### ②、到路由文件web.php里面添加一条新的路由：
    Route::get('category/getCategories','CategoryController@getCategories'); //注意将这条路由放在资源路由的什么，不然可能要报错
    Route::resource('category','CategoryController');
#### ③、到CategoryController这个控制器里面添加getCategories这个方法：
    public function getCategories()
    {
        $categories = Category::all();
        return $categories;
    }
### 2、添加根分类：
#### ①、修改CategoryComponent.vue文件后内容改变为如下：
    <template>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card card-default">
                        <div class="card-header">添加根分类</div>
                        <div class="card-body">
                            <input class="form-control" type="text" v-model="newCategory" @keyup.enter="addCategory()">
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-default">
                        <div class="card-header">分类列表</div>
    
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item" v-for="category in categories">{{ category.name }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    
    <script>
        export default {
            mounted() {
                this.getCategories();
            },
            data() {
                return {
                    categories:[],
                    newCategory:''
                }
            },
            methods: {
                getCategories() {  //获取数据库里面的所有根分类
                    axios.get('category/getCategories').then(res=> { //这里不要忘记了到路由里面添加该路由
                        this.categories = res.data;
                    }).catch(error=> {
                        throw error
                    })
                },
                addCategory() { //添加根目录分类
                    axios.post('category',{'name':this.newCategory}).then(res=> { //这里用的是资源路由，控制器里面的方法是store
                        this.newCategory = '';  //input框清空内容
                        this.getCategories()  //重新获取数据，将新增加的目录显示出来
                    }).catch(error=> {
                        throw error
                    })
                }
            }
        }
    </script>

#### ②、修改控制器里面的store方法为：
    public function store(Request $request)
    {
       Category::create(['name' => $request->input('name')]);
    }
### 3、使用vue递归组件展示子分类
#### ①、使用tinker添加子分类的测试数据：
> php artisan tinker   //进入tinker界面
> $category = factory('App\Category',1)->create();  //先创建一个分类，目前为止它是个根分类
> $category->parent_id = 1;  //将上面添加的这个根分类归到id为1的这个根分类
> $category->save();  //保存
#### ②、创建树：到现在为止，界面显示出来的分类仍然没有任何关系。只是把数据表中的所有节点全部列出来而已。为了体现出层级关系，需要构建树：
    public function getCategories()
    {
        //$categories = Category::get();
        $categories = Category::get()->toTree();
        return $categories;
    }
#### ③、！！重点！！，现在会发现，界面显示出来的节点就只有父节点了。那么如何实现层级显示出子节点呢？
##### 首先，另外创建一个名为CategoryTree.vue的组件，作用是用来循环调用的。内容如下：
    <template>
        <li class="list-group-item">
            {{ category.name }}
            <ul class="list-group" >
                <!--注意，调用本身组件，需要到app.js里面注册，注意下面的category.children-->
                <category-tree v-for="category in category.children" :key="category.id" :category="category"></category-tree>
            </ul>
        </li>
    </template>
    
    <script>
        export default {
            props: ['category']
        }
    </script>
##### 然后，到app.js里面注册上面的组件：
    Vue.component('category-tree', require('./components/categories/CategoryTree.vue'));
##### 再然后，修改CategoryComponent.vue的代码如下：
    <template>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card card-default">
                        <div class="card-header">添加根分类</div>
                        <div class="card-body">
                            <input class="form-control" type="text" v-model="newCategory" @keyup.enter="addCategory()">
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-default">
                        <div class="card-header">分类列表</div>
    
                        <div class="card-body">
                            <ul class="list-group">
                                <!--<li class="list-group-item" v-for="category in categories">{{ category.name }}</li>-->
                                <!--用这个循环组件替换上面这个<li>-->
                                <category-tree v-for="category in categories" :key="category.id" :category="category"></category-tree>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    
    <script>
        import CategoryTree from './CategoryTree.vue' //引入子组件
        export default {
            components: {
                'category-tree': CategoryTree  //注册子组件
            },
            mounted() {
                this.getCategories();
            },
            data() {
                return {
                    categories:[],
                    newCategory:''
                }
            },
            methods: {
                getCategories() {  //获取数据库里面的所有根分类
                    axios.get('category/getCategories').then(res=> { //这里不要忘记了到路由里面添加该路由
                        this.categories = res.data;
                    }).catch(error=> {
                        throw error
                    })
                },
                addCategory() { //添加根目录分类
                    axios.post('category',{'name':this.newCategory}).then(res=> { //这里用的是资源路由，控制器里面的方法是store
                        this.newCategory = '';  //input框清空内容
                        this.getCategories()  //重新获取数据，将新增加的目录显示出来
                    }).catch(error=> {
                        throw error
                    })
                }
            }
        }
    </script>

##### 再然后，调整列表显示的样式，修改CategoryTree.vue内容为：
    <template>
        <div>
            <li class="list-group-item">
                {{ category.name }}
            </li>
            <!--注意，这里将<li>与<ul>隔开，就是为了显示在下面-->
            <ul class="list-group child-group" >
                <!--注意，调用本身组件，需要到app.js里面注册，注意下面的category.children-->
                <category-tree v-for="category in category.children" :key="category.id" :category="category"></category-tree>
            </ul>
        </div>
    </template>
    <!--设置分类列表的显示样式-->
    <style lang="scss">
        .list-group-item {
            height: 4em;  //定义各个节点显示列表的高度
            border-left: 3px solid #ff9b44; //定义根节点显示列表左边框的宽度和颜色
        }
        .child-group {
            margin-left: 4em;  //定义子节点显示列表左边框的距离，体现出层级来
            li.list-group-item {
                border-left-color: #e3ff60; //定义子节点显示列表左边框的宽度和颜色
            }
        }
    </style>
    
    <script>
        export default {
            props: ['category']
        }
    </script>
### 4、在根分类列表里面添加按钮，实现添加子分类功能：
#### ①、修改CategoryTree.vue组件代码，添加按钮，修改后代码如下：
    <template>
        <div>
            <li class="list-group-item">
                <h6>{{ category.name }}</h6>
                <!--注意，bootstrap4之后，btn没有btn-xs了，只有自己定制了-->
                <button type="button" class="btn btn-default btn-sm">新增子类</button>
            </li>
            <!--注意，这里将<li>与<ul>隔开，就是为了显示在下面-->
            <ul class="list-group child-group" >
                <!--注意，调用本身组件，需要到app.js里面注册，注意下面的category.children-->
                <category-tree v-for="category in category.children" :key="category.id" :category="category"></category-tree>
            </ul>
        </div>
    </template>
    <!--设置分类列表的显示样式-->
    <style lang="scss">
        .list-group-item {
            height: 3em;  //定义各个节点显示列表的高度
            border-left: 3px solid #ff9b44; //定义根节点显示列表左边框的宽度和颜色
        }
        .child-group {
            margin-left: 4em;  //定义子节点显示列表左边框的距离，体现出层级来
            li.list-group-item {
                border-left-color: #e3ff60; //定义子节点显示列表左边框的宽度和颜色
            }
        }
    </style>
    
    <script>
        export default {
            props: ['category']
        }
    </script>

#### ②、发现bootstrap4.0后的版本按钮不支持xs，按钮始终很大，为了解决这个问题，我们需要定制它：
因为我们发现，没有btn-xs，只有btn-lg或btn-sm，但是btn-sm仍然比较大，那么如何来定制，让它变小呢？方法如下：
只要在resources/assets/sass/_variables.scss文件里面添加如下代码即可，因为这文件就是来定制开发用的：
      
      //button
      $btn-padding-y-sm:0.25rem;
      $btn-padding-x-sm:0.25rem;
      $font-size-sm:0.555rem;
      $btn-line-height-sm:1;
      $btn-border-radius-sm:0.15rem;

那么如何找到$btn-padding-y-sm这个东西呢？就需要到node_modules里面的bootstrap/scss/_buttons.scss里面输入btn-sm即可查找到。

