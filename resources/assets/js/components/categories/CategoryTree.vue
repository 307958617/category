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
