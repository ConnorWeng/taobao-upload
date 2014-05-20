KISSY.add(function(S) {

    var args = S.makeArray(arguments);
    S.ready(function() {
        // 模块初始化
        for (var i = 1; i < args.length; i++) {
            var module = args[i] || {};
            module.init && module.init();
        }
    });

}, {requires:[
    'v2/source/atpanel',
    // 表单提交相关逻辑
    'v2/source/publish/forms',
    // 额外脚本的引入执行
    'v2/source/publish/extra',
    // 属性值投票相关逻辑 (功能下线)
    //'v2/source/publish/prop_vote',
    // 宝贝标题相关逻辑
    'v2/source/publish/itemtitle',
    // 宝贝描述逻辑（编辑器逻辑）
    'v2/source/publish/desc',
    // 多图模块逻辑
    //'v2/source/publish/upload/itempic',
    'v2/source/publish/multimages',
    // 分阶段付款
    'v2/source/publish/paytype',
    // sku逻辑
    'v2/source/publish/sku',
    // 商品扩展结构初始逻辑
    'v2/source/publish/extend_info',
    // kfc（宝贝标题和描述 违禁词）逻辑
    'v2/source/publish/kfc',
    // 拍卖部分相关逻辑
    'v2/source/publish/auction',
    // 提取方式，包括电子凭证、运费模板、线下支付
    'v2/source/publish/transport',
    // 尺码库
    'v2/source/publish/size-template',
    // 地址逻
    'v2/source/publish/location',
    // 小功能逻辑集合
    'v2/source/publish/page',
    // 度量衡逻辑
    'v2/source/publish/measurement',
    // 上架时间逻辑
    'v2/source/publish/saletime',
    // 自动发售逻辑
    'v2/source/publish/spec-autosale',
    // 售后说明逻辑和保修逻辑
    'v2/source/publish/aftersale',
    // 商品店铺分类逻辑
    'v2/source/publish/shopcategories',
    // 单图逻辑
    'v2/source/publish/singlepic',
    // 特殊类目，食品相关逻辑
    'v2/source/publish/spec-food',
    // 食品安全协议签署逻辑
    'v2/source/publish/spec-food-protocol',
    // 视频逻辑
    'v2/source/publish/video',
    // 宝贝属性相关逻辑
    'v2/source/publish/itemproperties',
    // spu逻辑
    'v2/source/publish/itemproperties/spu',
    // 房产类目，结算关系逻辑
    'v2/source/publish/houseproxy',
    // 服务保障
    'v2/source/publish/serviceAssurance',
    // 宝贝类型逻辑
    'v2/source/publish/itemtype',
    // 号码库宝贝逻辑
    'v2/source/publish/spec-number',
    // 公共属性子属性多选
    'v2/source/publish/multicheck',
    'v2/source/publish/multichecktree',
    // 宝贝体验页面带入错误信息展示。
    'v2/source/publish/noticeguider',
    // 定金支持业务
    'v2/source/publish/reservation',
    // 本地生活分类信息发布业务
    'v2/source/publish/sort_extra',
    // 新农业相关业务
    'v2/source/publish/farm-prop',
    // 书籍类目ISBN信息效验以及添加
    'v2/source/publish/book-isbn',
    // 本地生活：宝贝与本地商户关联关系
    'v2/source/publish/relateoffline',
    // 浮动提示
    'v2/source/publish/easytip',
    // 多选列表。（不包含子属性，目前应用于服务发布系统的服务人员选择上）。
    // 目前暂无应用
    //'v2/source/publish/checklist',
    // 服务市场相关业务：添加案例。
    'v2/source/publish/case',
    // 话费类目，针对spu设置固定的主图
    'v2/source/publish/spuimage',
    // 发布助手（规则前置）
    'v2/source/publish/helper',
    // bc贯通
    'v2/source/publish/supplymatch/index',
    // 房产类目，房产优惠卷需求
    'v2/source/publish/houselink',
    // 针对航旅项目的错误提示，将发布助手屏蔽掉。
    'v2/source/publish/originform',
    // 门票商品发布前的协议签署
    'v2/source/publish/protocol',
    // 门票，酒店类目的价格中心组件
    'v2/source/publish/pricecenter',
    // 标签竞价功能
    'v2/source/publish/tag',
    // 本地生活商品发布
    'v2/source/publish/life',
    // 付款方式
    'v2/source/publish/paymethod',
    // 采购地
    'v2/source/publish/stockAddr',
    // 定制支持
    'v2/source/publish/customizeSupport',
    // 宝贝卖点
    'v2/source/publish/subheading'
    ]}
);
