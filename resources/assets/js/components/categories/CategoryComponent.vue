<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card card-default">
                    <div class="card-header">添加根分类</div>
                    <div class="card-body">
                        <input class="form-control" type="text" v-model="newCategory" @keyup.enter="addCategory()">
                        <br>
                        <button class="btn btn-success btn-block">更新设置</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">
                        分类列表
                        <span class=" pull-right">
                            <button class="btn btn-sm " :class="[showChildren ? 'btn-danger' : 'btn-success']" @click="toggleShowChildren()">{{ showChildren ? '折叠所有' : '展开所有'}}</button>
                        </span>
                    </div>

                    <div class="card-body">
                        <ul class="list-group">
                            <!--<li class="list-group-item" v-for="category in categories">{{ category.name }}</li>-->
                            <!--用这个循环组件替换上面这个<li>，-->
                            <!--category-tree这组件里面可以用this.$emit('getCategories');来调用本父组件的getCategories方法-->
                            <category-tree :showChild="showChildren" @onPropsChange="change" @getCategories="getCategories" v-for="category in categories" :key="category.id" :category="category"></category-tree>
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
                newCategory:'',
                showChildren:false //表示是否显示子列表
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
            },
            toggleShowChildren() {
                this.showChildren = !this.showChildren
            },
            change(propName,newVal,oldVal) {
                this[propName]=newVal;
            }
        }
    }
</script>
