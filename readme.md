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
> php artisan tinker;   //进入tinker界面

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
### 4、在根分类列表里面添加按钮组，实现添加分类、编辑分类、插入分类（向下或向上）、移动分类、删除分类功能：
#### 在此发现一个问题：bootstrap4.0后的版本按钮不支持xs，因为我们发现，没有btn-xs，只有btn-lg或btn-sm，但是btn-sm仍然比较大，那么如何来定制，让它变小呢，我们有两个方法来解决它：
##### 方法1：我们来定制它：方法如下：只要在resources/assets/sass/_variables.scss文件里面添加如下代码即可，因为这文件就是来定制开发用的：  
      //button
      $btn-padding-y-sm:0.25rem;
      $btn-padding-x-sm:0.25rem;
      $font-size-sm:0.555rem;
      $btn-line-height-sm:1;
      $btn-border-radius-sm:0.15rem;

那么如何找到$btn-padding-y-sm这个东西呢？就需要到node_modules里面的bootstrap/scss/_buttons.scss里面输入btn-sm即可查找到。
##### 方法2：我们发现虽然按钮没有btn-xs但是按钮组却有.btn-group-xs，所以将按钮放入按钮组即可实现：
    <div class="btn-group btn-group-xs">
      <button type="button" class="btn btn-primary">Apple</button>
      <button type="button" class="btn btn-primary">Samsung</button>
      <button type="button" class="btn btn-primary">Sony</button>
    </div>
#### ①、实现 添加分类 功能：
##### 步骤1：在分类列表下面添加一个按钮组，并向其中添加一个 增加分类 按钮：
    <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-success">增加分类</button>
    </div>
##### 步骤2、实现鼠标放在列表上面显示出按钮组：
修改CategoryTree.vue实现鼠标放在列表上面显示出按钮组的功能，修改后代码如下：
    
    <template>
        <div>
            <!--增加@mouseover和@mouseleave两个事件，来触发按钮组是否显示-->
            <li class="list-group-item" @mouseover="showOptions=true" @mouseleave="showOptions=false">
                <h6>{{ category.name }}</h6>
                <!--注意，bootstrap4之后，btn没有btn-xs了，只有自己定制了-->
                <!--为按钮组添加v-if判断，来判断是否显示按钮组-->
                <div class="btn-group btn-group-sm" v-if="showOptions">
                    <button type="button" class="btn btn-success">增加子类</button>
                </div>
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
            height: 5em;  //定义各个节点显示列表的高度
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
            props: ['category'],
            data() {
                return {
                    newCategory:'',
                    showOptions: false //用来判断是否显示按钮组
                }
            }
        }
    </script>
##### 步骤3、实现 点击 增加分类 按钮弹出一个增加分类的模态框：
###### 首先、在resources/assets/js/components/categories里面新建一个vue.js的模态框CategoryModel.vue内容如下：
    <template>
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
    
                        <div class="modal-header">
                            <slot name="header">
                                default header
                              </slot>
                        </div>
    
                        <div class="modal-body">
                            <slot name="body">
                                default body
                              </slot>
                        </div>
    
                        <div class="modal-footer">
                            <slot name="footer">
                                default footer
                                <button class="modal-default-button" @click="$emit('close')">
                                OK
                              </button>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
    <!--设置模态框的显示样式-->
    <style media="screen">
        .modal-mask {
            position: fixed;
            z-index: 9998;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, .5);
            display: table;
            transition: opacity .3s ease;
        }
    
        .modal-wrapper {
            display: table-cell;
            vertical-align: middle;
        }
    
        .modal-container {
            width: 300px;
            margin: 0px auto;
            padding: 20px 30px;
            background-color: #fff;
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
            transition: all .3s ease;
            font-family: Helvetica, Arial, sans-serif;
        }
    
        .modal-header h3 {
            margin-top: 0;
            color: #42b983;
        }
    
        .modal-body {
            margin: 20px 0;
        }
    
        .modal-default-button {
            float: right;
        }
    
        /*
         * The following styles are auto-applied to elements with
         * transition="modal" when their visibility is toggled
         * by Vue.js.
         *
         * You can easily play with the modal transition by editing
         * these styles.
         */
    
        .modal-enter {
            opacity: 0;
        }
    
        .modal-leave-active {
            opacity: 0;
        }
    
        .modal-enter .modal-container,
        .modal-leave-active .modal-container {
            -webkit-transform: scale(1.1);
            transform: scale(1.1);
        }
    </style>
    
    <script>
    
    </script>
###### 然后：注册CategoryModel模态框,到app.js添加如下代码
    Vue.component('category-model', require('./components/categories/CategoryModel.vue'));
###### 再然后：修改CategoryTree.vue，实现点击显示模态框,点击模态框取消按钮隐藏模态框，修改后代码如下：
    <template>
        <div>
            <!--增加@mouseover和@mouseleave两个事件，来触发按钮组是否显示-->
            <li class="list-group-item" @mouseover="showOptions=true" @mouseleave="showOptions=false">
                <h6>{{ category.name }}</h6>
                <!--注意，bootstrap4之后，btn没有btn-xs了，只有自己定制了-->
                <!--为按钮组添加v-if判断，来判断是否显示按钮组-->
                <div class="btn-group btn-group-sm" v-if="showOptions">
                    <!--给按钮添加一个方法用来显示模态框的-->
                    <button type="button" class="btn btn-success" @click="showModel=true">增加子类</button>
                </div>
            </li>
            <!--注意，这里将<li>与<ul>隔开，就是为了显示在下面-->
            <ul class="list-group child-group" >
                <!--注意，调用本身组件，需要到app.js里面注册，注意下面的category.children-->
                <category-tree v-for="category in category.children" :key="category.id" :category="category"></category-tree>
            </ul>
            <!--引入模态框，并控制显示与否，并根据不同情况显示不同的solt-->
            <category-model v-if="showModel">
                <h3 slot="header">增加子类</h3>
                <input class="form-control" slot="body" type="text" v-model="newCategory">
                <button class="btn btn-sm btn-success" slot="footer">保存</button>
                <!--实现点击取消按钮，隐藏模态框-->
                <button class="btn btn-sm btn-default" slot="footer" @click="showModel=false">取消</button>
            </category-model>
        </div>
    </template>
    <!--设置分类列表的显示样式-->
    <style lang="scss">
        .list-group-item {
            height: 5em;  //定义各个节点显示列表的高度
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
        import CategoryModel from './CategoryModel.vue' //引入模态框
        export default {
            components:{
                'category-model':CategoryModel
            },
            props: ['category'],
            data() {
                return {
                    newCategory:'',
                    showOptions: false, //用来判断是否显示按钮组
                    showModel: false, //用来判断是否显示模态框
                }
            }
        }
    </script>
###### 再再然后，修改CategoryTree.vue，实现点击保存按钮，将新的子类保存到数据库，修改后代码如下：
    <template>
        <div>
            <!--增加@mouseover和@mouseleave两个事件，来触发按钮组是否显示-->
            <li class="list-group-item" @mouseover="showOptions=true" @mouseleave="showOptions=false">
                <h6>{{ category.name }}</h6>
                <!--注意，bootstrap4之后，btn没有btn-xs了，只有自己定制了-->
                <!--为按钮组添加v-if判断，来判断是否显示按钮组-->
                <div class="btn-group btn-group-sm" v-if="showOptions">
                    <!--给按钮添加一个方法用来显示模态框的-->
                    <button type="button" class="btn btn-success" @click="showModel=true">增加子类</button>
                </div>
            </li>
            <!--注意，这里将<li>与<ul>隔开，就是为了显示在下面-->
            <ul class="list-group child-group" >
                <!--注意，调用本身组件，需要到app.js里面注册，注意下面的category.children-->
                <!--同时这里也不要忘记添加方法@getCategories="getCategories"-->
                <category-tree @getCategories="getCategories" v-for="category in category.children" :key="category.id" :category="category"></category-tree>
            </ul>
            <!--引入模态框，并控制显示与否，并根据不同情况显示不同的solt-->
            <category-model v-if="showModel">
                <h3 slot="header">给"{{category.name}}"增加子类</h3>
                <input class="form-control" slot="body" type="text" v-model="newCategory">
                <!--实现点击保存按钮，将新的子类保存到数据库-->
                <button class="btn btn-sm btn-success" slot="footer" @click="addChildCategory()">保存</button>
                <!--实现点击取消按钮，隐藏模态框-->
                <button class="btn btn-sm btn-default" slot="footer" @click="showModel=false">取消</button>
            </category-model>
        </div>
    </template>
    <!--设置分类列表的显示样式-->
    <style lang="scss">
        .list-group-item {
            height: 5em;  //定义各个节点显示列表的高度
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
        import CategoryModel from './CategoryModel.vue' //引入模态框
        export default {
            components:{
                'category-model':CategoryModel
            },
            props: ['category'],
            data() {
                return {
                    newCategory:'',
                    showOptions: false, //用来判断是否显示按钮组
                    showModel: false, //用来判断是否显示模态框
                }
            },
            methods:{
                addChildCategory() {  //提交新添加的分类到数据
                    //这需要创建新的路由，并创建新的方法用来保存新的子分类
                    axios.post('/category/addChildCategory',{parentId:this.category.id,name:this.newCategory}).then(res=>{
                        this.newCategory = '';
                        this.showModel = false;
                        //调用父组件的方法，实现添加新分类后马上显示出来，但是不要忘记到父组件里面添加这个方法@getCategories="getCategories"
                        //<category-tree @getCategories="getCategories" v-for="category in categories" :key="category.id" :category="category"></category-tree>
                        this.getCategories();
                    }).catch(error=> {
                        throw error
                    });
                },
                getCategories() { //必须增加这个方法与父组件的名称一样这很重要，本组件递归调用才不会报错
                    this.$emit('getCategories');
                }
            }
        }
    </script>
###### 再再再然后，创建新的路由，并到CategoryController控制器里面创建新的方法用来保存新的子分类：
> 路由：
    
    Route::post('category/addChildCategory','CategoryController@addChildCategory');
    
> 控制器方法：
    
    public function addChildCategory(Request $request)
    {
        $request->validate([   //验证数据
            'name' => 'required|unique:categories',
        ]);
        $category = Category::create(['name' =>$request->input('name')]);
        //将上面添加的节点设置一个父节点
        $category->parent_id = $request->input('parentId');
        $category->save();
    }
#### ②、实现 编辑分类 功能：
##### 步骤1：找到分类列表存在的按钮组，并向其中添加一个 编辑分类 按钮：
    <button type="button" class="btn btn-primary" @click="showModel=true">编辑分类</button>
### ！！那么问题出来了！！如何使编辑分类也复用上面创建的模态框呢？
##### 步骤2：修改CategoryTree.vue，将实现增加分类和编辑分类的模态框复用：
###### 首先、在data里面添加编辑模态框需要的数据：
    editCategory:this.category.name,//给编辑的输入框绑定的
    modal:{  //定义模态框需要的数据
        headerDescription:'',//表示模态框头部显示的信息
        vModel:'',//表示模态框里面的input需要绑定的数据是哪个
        method:''// 表示模态框里面的提交数据的方法是什么
    }
###### 然后、需要添加一个openModel(type)的方法，通过type是什么来判断调用哪个模态框（是增加还是编辑）：
    openModel(type) {
        this.showModel=true;//每次都要显示出模态框
        switch(type) {
            case 'add':
                this.modal = {
                    headerDescription:'增加子分类',
                    vModel:'newCategory',
                    method:'addChildCategory'
                };
                break;
            case 'edit':
                this.modal = {
                    headerDescription:'修改分类名称',
                    vModel:'editCategory',
                    method:'updateCategory'
                };
                break;
            default:
        }
    }
###### 再然后、修改模态框组件<category-model></category-model>里面的内容如下：
    <category-model v-if="showModel">
        <!--根据不同情况显示不同的头部信息-->
        <h3 slot="header">给"{{ category.name }}"{{ modal.headerDescription }}</h3>
        <!--实现增加分类-->
        <input v-if="modal.vModel === 'newCategory'" class="form-control" slot="body" type="text" v-model="newCategory" >
        <button v-if="modal.method === 'addChildCategory'" class="btn btn-sm btn-success" slot="footer" @click="addChildCategory">保存</button>
        <!--实现编辑分类-->
        <input v-if="modal.vModel === 'editCategory'" class="form-control" slot="body" type="text" v-model="editCategory" >
        <button v-if="modal.method === 'updateCategory'" class="btn btn-sm btn-success" slot="footer" @click="updateCategory">保存</button>
        <!--实现点击取消按钮，隐藏模态框-->
        <button class="btn btn-sm btn-default" slot="footer" @click="showModel=false">取消</button>
    </category-model>
##### 步骤3：增加编辑分类名称的方法updateCategory，该方法具体内容如下：
    updateCategory() {
        //这里使用资源路由即可注意是put方法对编辑应控制器里面的update方法即可
        axios.put('/category/'+this.category.id,{name:this.editCategory}).then(res=>{
            this.showModel = false;
            //调用父组件的方法，实现添加新分类后马上显示出来，但是不要忘记到父组件里面添加这个方法@getCategories="getCategories"
            this.getCategories();
            //this.editCategory = this.category.name;必须放到this.getCategories();的后面，从才能显示出修改后的名称。
            //但是这样之后，还是显示是修改之前的名称，修改之后的名称没有显示到input框里是为什么？思考一下。
            this.editCategory = this.category.name;
        }).catch(error=> {
            throw error
        });
    },
#### ③、实现 删除分类 功能：
##### 步骤1：找到分类列表存在的按钮组，并向其中添加一个 删除分类 按钮：
    <!--给按钮添加一个方法直接删除当前分类及该分类下的所有子类-->
    <button type="button" class="btn btn-danger" @click="deleteCategory">删除分类</button>
##### 步骤2：增加deleteCategory()这个方法：
    deleteCategory() {
        //这里使用资源路由即可注意是delete方法对编辑应控制器里面的destroy方法即可
        axios.delete('/category/'+this.category.id).then(res=>{
            //调用父组件的方法，实现添加新分类后马上显示出来，但是不要忘记到父组件里面添加这个方法@getCategories="getCategories"
            this.getCategories();
        }).catch(error=> {
            throw error
        });
    },
##### 步骤3：编写控制器的destroy方法：
    public function destroy(Category $category)
    {
        /**
         * 删掉一个节点：

        $node->delete();

        * **注意！**节点的所有后代将一并删除 注意！ 节点需要向模型一样删除，不能使用下面的语句来删除节点：

        Category::where('id', '=', $id)->delete();
         */
        $category->delete();
    }
#### ④、实现如果这个节点下面有子节点，就让上面的删除按钮不显示出来：
    <!--添加判断是否显示删除按钮v-if="category.children.length===0"-->
    <button type="button" class="btn btn-danger" @click="deleteCategory" v-if="category.children.length===0">删除分类</button>
