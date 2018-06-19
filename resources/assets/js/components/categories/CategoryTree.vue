<template>
    <div>
        <!--增加@mouseover和@mouseleave两个事件，来触发按钮组是否显示-->
        <li class="list-group-item" @mouseover="showOptions=true" @mouseleave="showOptions=false">
            <h6>{{ category.name }}</h6>
            <!--注意，bootstrap4之后，btn没有btn-xs了，只有自己定制了-->
            <!--为按钮组添加v-if判断，来判断是否显示按钮组-->
            <div class="btn-group btn-group-sm" v-if="showOptions">
                <!--给按钮添加一个方法用来显示添加分类模态框的-->
                <button type="button" class="btn btn-success" @click="openModel('add')">增加子类</button>
                <!--给按钮添加一个方法用来显示编辑分类模态框的-->
                <button type="button" class="btn btn-primary" @click="openModel('edit')">编辑分类</button>
                <!--给按钮添加一个方法直接删除当前分类及该分类下的所有子类-->
                <!--添加判断是否显示删除按钮v-if="canDeleteCategory"-->
                <button type="button" class="btn btn-danger" @click="deleteCategory" v-if="canDeleteCategory">删除分类</button>
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
                canDeleteCategory:false,//判断是否显示删除按钮
                newCategory:'',//给新增的输入框绑定的
                editCategory:this.category.name,//给编辑的输入框绑定的
                showOptions: false, //用来判断是否显示按钮组
                showModel: false, //用来判断是否显示模态框
                modal:{  //定义模态框需要的数据
                    headerDescription:'',//表示模态框头部显示的信息
                    vModel:'',//表示模态框的绑定数据是什么
                    method:''// 表示模态框里面的提交数据的方法是什么
                }
            }
        },
        mounted() {
            this.getCategories()
        },
        methods:{
            addChildCategory() {  //提交新添加的分类到数据
                //这需要创建新的路由，并创建新的方法用来保存新的子分类
                axios.post('/category/addChildCategory',{parentId:this.category.id,name:this.newCategory}).then(res=>{
                    this.newCategory = '';
                    this.showModel = false;
                    this.canDeleteCategory = false;
                    //调用父组件的方法，实现添加新分类后马上显示出来，但是不要忘记到父组件里面添加这个方法@getCategories="getCategories"
                    //<category-tree @getCategories="getCategories" v-for="category in categories" :key="category.id" :category="category"></category-tree>
                    this.getCategories();
                }).catch(error=> {
                    throw error
                });
            },
            deleteCategory() {
                //这里使用资源路由即可注意是delete方法对编辑应控制器里面的destroy方法即可
                axios.delete('/category/'+this.category.id).then(res=>{
                    //调用父组件的方法，实现添加新分类后马上显示出来，但是不要忘记到父组件里面添加这个方法@getCategories="getCategories"
                    this.getCategories();
                }).catch(error=> {
                    throw error
                });
            },
            updateCategory() {
                //这里使用资源路由即可注意是put方法对编辑应控制器里面的update方法即可
                axios.put('/category/'+this.category.id,{name:this.editCategory}).then(res=>{
                    this.showModel = false;
                    //调用父组件的方法，实现添加新分类后马上显示出来，但是不要忘记到父组件里面添加这个方法@getCategories="getCategories"
                    this.getCategories();
                    //this.editCategory = this.category.name;必须放到this.getCategories();的后面，从才能显示出修改后的名称。
                    this.editCategory = this.category.name;
                }).catch(error=> {
                    throw error
                });
            },
            getCategories() { //必须增加这个方法与父组件的名称一样这很重要，本组件递归调用才不会报错
                this.$emit('getCategories');
                //判断当前节点是否有子节点
                axios.get('/category/'+this.category.id).then(res=>{
                    if(res.data.children === 0) {
                        this.canDeleteCategory = true;
                    }
                }).catch(error=> {
                    throw error
                });
            },
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
        }
    }
</script>
