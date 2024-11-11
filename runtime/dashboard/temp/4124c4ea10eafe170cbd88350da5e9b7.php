<?php /*a:4:{s:64:"D:\phpEnv\www\github\daisysms\app\dashboard\view\index\main.html";i:1731296062;s:66:"D:\phpEnv\www\github\daisysms\app\dashboard\view\common\__top.html";i:1731296062;s:67:"D:\phpEnv\www\github\daisysms\app\dashboard\view\common\__left.html";i:1731296062;s:69:"D:\phpEnv\www\github\daisysms\app\dashboard\view\common\__footer.html";i:1731296062;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <title>Dashboard - daisySMS</title>

    <link rel="stylesheet" type="text/css" href="/static/css/_main.css?v=f90b0b14e89c3f9d28d64d85d8b45148">

    <link rel="stylesheet" type="text/css" href="/static/css/_notie.min.css">


    <script src="/static/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/static/js/plugins/validate/bootstrapValidator.min.js"></script>
    <script src="/static/js/plugins/validate/zh_CN.js"></script>
    <script src="/static/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
    <script src="/static/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
    <script src="/static/js/plugins/layer/layer.min.js"></script>
    <script src="/static/js/plugins/layer/laydate/laydate.js"></script>
    <script src="/static/js/common/ajax-object.js?v=<?php echo rand(1000,9999)?>"></script>
    <script src="/static/js/common/bootstrap-table-object.js"></script>
    <script src="/static/js/common/Feng.js"></script>
    <script src="/static/js/plugins/webuploader/webuploader.min.js"></script>
    <script type="text/javascript" src="/static/js/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="/static/js/ueditor/ueditor.all.min.js"> </script>
    <script type="text/javascript" src="/static/js/xheditor/xheditor-1.2.2.min.js"></script>
    <script type="text/javascript" src="/static/js/xheditor/xheditor_lang/zh-cn.js"></script>

    <style>.notie-container {z-index: 20;}</style>

    <style>
        .phone::before {
            content: "+1 ";
            user-select: none;
            opacity: 0.5;
        }
        td[data-field="name"] {
            max-width: 300px; /* 设置最大宽度 */
            word-wrap: break-word; /* 启用自动换行 */
            white-space: normal; /* 确保换行生效 */
        }
    </style>

</head>
<body data-theme="light">

<!--左侧导航开始-->
<div class="fixed z-20 w-full flex flex-col">
<!--    <link href="/static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">-->
    <style>.fixed-table-pagination > .pull-left.pagination-detail{display: none}.pull-right{float:right;}</style>

    <div class="navbar bg-base-100 border-b">
        <div class="navbar-start">
            <div class="dropdown">
                <label tabindex="0" class="btn btn-ghost lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                </label>
                <ul tabindex="0" class="menu menu-compact dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
                    <li><a href="/dashboard">Dashboard</a></li>

                    <li><a href="/public#features">Features</a></li>
                    <li><a href="/public#pricing">Pricing</a></li>
                    <li><a href="/docs/api">API</a></li>

                    <li><a href="/dashboard/referrals">Referrals</a></li>
                    <li><a href="/dashboard/wallet">Wallet</a></li>
                    <li><a href="/dashboard/services">Services</a></li>
                    <li><a href="/dashboard/profile">Profile</a></li>

                    <li><a href="https://t.me/+AB_a7ELZ8kg1NDEy" target="_blank">News <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="24px" height="24px"><path d="M39.175,10.016c1.687,0,2.131,1.276,1.632,4.272c-0.571,3.426-2.216,14.769-3.528,21.83 c-0.502,2.702-1.407,3.867-2.724,3.867c-0.724,0-1.572-0.352-2.546-0.995c-1.32-0.872-7.984-5.279-9.431-6.314 c-1.32-0.943-3.141-2.078-0.857-4.312c0.813-0.796,6.14-5.883,10.29-9.842c0.443-0.423,0.072-1.068-0.42-1.068 c-0.112,0-0.231,0.034-0.347,0.111c-5.594,3.71-13.351,8.859-14.338,9.53c-0.987,0.67-1.949,1.1-3.231,1.1 c-0.655,0-1.394-0.112-2.263-0.362c-1.943-0.558-3.84-1.223-4.579-1.477c-2.845-0.976-2.17-2.241,0.593-3.457 c11.078-4.873,25.413-10.815,27.392-11.637C36.746,10.461,38.178,10.016,39.175,10.016 M39.175,7.016L39.175,7.016 c-1.368,0-3.015,0.441-5.506,1.474L33.37,8.614C22.735,13.03,13.092,17.128,6.218,20.152c-1.074,0.473-4.341,1.91-4.214,4.916 c0.054,1.297,0.768,3.065,3.856,4.124l0.228,0.078c0.862,0.297,2.657,0.916,4.497,1.445c1.12,0.322,2.132,0.478,3.091,0.478 c1.664,0,2.953-0.475,3.961-1.028c-0.005,0.168-0.001,0.337,0.012,0.507c0.182,2.312,1.97,3.58,3.038,4.338l0.149,0.106 c1.577,1.128,8.714,5.843,9.522,6.376c1.521,1.004,2.894,1.491,4.199,1.491c2.052,0,4.703-1.096,5.673-6.318 c0.921-4.953,1.985-11.872,2.762-16.924c0.331-2.156,0.603-3.924,0.776-4.961c0.349-2.094,0.509-4.466-0.948-6.185 C42.208,7.875,41.08,7.016,39.175,7.016L39.175,7.016z"/></svg>
                    </a></li>
                </ul>
            </div>
            <a href="/public" class="btn btn-ghost normal-case text-xl">daisySMS</a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                <li><a href="/public#features">Features</a></li>
                <li><a href="/public#pricing">Pricing</a></li>
                <li><a href="/docs/api">API</a></li>
            </ul>
        </div>

        <div class="navbar-end">
            <div id="webpush" class="group flex mr-6 cursor-pointer off">
                <a id="subscribe" class="hidden tooltip tooltip-left group-[.off]:block" data-tip="Enable push notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </a>

                <a id="unsubscribe" class="tooltip tooltip-left group-[.off]:hidden" data-tip="Disable push notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell-off"><path d="M13.73 21a2 2 0 0 1-3.46 0"></path><path d="M18.63 13A17.89 17.89 0 0 1 18 8"></path><path d="M6.26 6.26A5.86 5.86 0 0 0 6 8c0 7-3 9-3 9h14"></path><path d="M18 8a6 6 0 0 0-9.33-5"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </a>
            </div>

            <a href="/dashboard/wallet" id="balance" class="btn btn-success mr-2 font-mono">
                <?php if($uMoney): ?>$ <?php echo $uMoney; else: ?>$ 0.00<?php endif; ?>
            </a>

            <a class="btn btn-info hidden sm:flex" href="/dashboard/login/out">Log out</a>
        </div>
    </div>
</div>
<!--左侧导航结束-->

<div class="py-8"></div>

<div class="flex flex-col lg:flex-row h-full">

    <!--左侧导航开始-->
    <ul class="menu bg-base-100 pt-8 border-r hidden xl:flex">
    <li>
        <a class="px-8" href="/dashboard/index">Dashboard</a>
    </li>
    <li>
        <a class="px-8" href="/dashboard/wallet">Wallet</a>
    </li>



    <li>
        <span class="px-8">History</span>

        <ul class="bg-base-100 z-20 border">
            <li><a id="stats_link" href="/dashboard/history/stats?tz=-480">Stats</a></li>
            <li><a href="/dashboard/history/payments">Payments</a></li>
            <li><a href="/dashboard/history/rentals">Rentals</a></li>
        </ul>
    </li>
    <li>
        <a class="px-8" href="/dashboard/referrals">Referrals</a>
    </li>
    <li>
        <a class="px-8" href="javascript:void(0);">News <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px"><path d="M39.175,10.016c1.687,0,2.131,1.276,1.632,4.272c-0.571,3.426-2.216,14.769-3.528,21.83 c-0.502,2.702-1.407,3.867-2.724,3.867c-0.724,0-1.572-0.352-2.546-0.995c-1.32-0.872-7.984-5.279-9.431-6.314 c-1.32-0.943-3.141-2.078-0.857-4.312c0.813-0.796,6.14-5.883,10.29-9.842c0.443-0.423,0.072-1.068-0.42-1.068 c-0.112,0-0.231,0.034-0.347,0.111c-5.594,3.71-13.351,8.859-14.338,9.53c-0.987,0.67-1.949,1.1-3.231,1.1 c-0.655,0-1.394-0.112-2.263-0.362c-1.943-0.558-3.84-1.223-4.579-1.477c-2.845-0.976-2.17-2.241,0.593-3.457 c11.078-4.873,25.413-10.815,27.392-11.637C36.746,10.461,38.178,10.016,39.175,10.016 M39.175,7.016L39.175,7.016 c-1.368,0-3.015,0.441-5.506,1.474L33.37,8.614C22.735,13.03,13.092,17.128,6.218,20.152c-1.074,0.473-4.341,1.91-4.214,4.916 c0.054,1.297,0.768,3.065,3.856,4.124l0.228,0.078c0.862,0.297,2.657,0.916,4.497,1.445c1.12,0.322,2.132,0.478,3.091,0.478 c1.664,0,2.953-0.475,3.961-1.028c-0.005,0.168-0.001,0.337,0.012,0.507c0.182,2.312,1.97,3.58,3.038,4.338l0.149,0.106 c1.577,1.128,8.714,5.843,9.522,6.376c1.521,1.004,2.894,1.491,4.199,1.491c2.052,0,4.703-1.096,5.673-6.318 c0.921-4.953,1.985-11.872,2.762-16.924c0.331-2.156,0.603-3.924,0.776-4.961c0.349-2.094,0.509-4.466-0.948-6.185 C42.208,7.875,41.08,7.016,39.175,7.016L39.175,7.016z"></path></svg>
        </a>
    </li>



    <li>
        <a class="px-8" href="/dashboard/services">Services</a>
    </li>
    <li>
        <a class="px-8" href="/dashboard/profile">Profile</a>
    </li>

</ul>
    <!--左侧导航结束-->

    <div class="flex flex-col p-8 border-r">
        <h2 class="card-title mb-2">SMS Verifications</h2>

<!--        <div class="text-stone-500">-->
<!--            <p>Rent a phone for 7 minutes.</p>-->
<!--            <p>Credits are only used if you receive the SMS code.</p>-->
<!--        </div>-->

        <div class="flex flex-row pt-4">
            <div class="input-group">
                <label class="input-group">
                    <span>Service</span>
                    <input id="service_filter" type="text" class="input input-bordered w-full" />

                    <button class="btn btn-square btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </label>
            </div>

            <button class="btn btn-square btn-success ml-2" id="toggle-settings">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            </button>
        </div>

        <div id="settings" class="mt-4 hidden">
            <div class="input-group">
                <span class="w-40">Area codes</span>
                <input id="area_codes" type="text" class="input input-bordered w-full" placeholder="503, 202, 404" />
            </div>
            <p class="text-xs opacity-60 py-2 px-4">Preferred area codes. Increases price by 20%.</p>

            <div class="input-group mt-2">
                <span class="w-40">Carriers</span>
                <input id="carriers" type="text" class="input input-bordered w-full" placeholder="tmo, vz, att" />
            </div>
            <p class="text-xs opacity-60 py-2 px-4">Preferred carrier. Increases price by 20%. <a class="link" href="#carrier-info">More info</a></p>
        </div>

        <div class="overflow-x-auto h-40 xl:h-96 mt-4">
            <table id="CodeGoodsTable" data-mobile-responsive="true" data-click-to-select="true" class="table w-full">
                <thead><tr><th data-field="selectItem" data-checkbox="true"></th></tr></thead>
            </table>
            
            <script>
                var CodeGoods = {id: "CodeGoodsTable",seItem: null,table: null,layerIndex: -1};

                CodeGoods.initColumn = function () {
                    return [
                        // {field: 'selectItem', checkbox: false},
                        {title: '编号', field: 'id', visible: false, align: 'left', valign: 'middle',sortable: false},
                        {title: 'Service', field: 'name', visible: true, align: 'left', valign: 'middle',sortable: false,class:'service cursor-pointer hover:bg-gray-100 select-none',formatter:CodeGoods.servicesFormatter},
                        {title: 'Price', field: 'cost', visible: true, align: 'left', valign: 'middle',sortable: false},
                        {title: '', field: '', visible: true, align: 'left', valign: 'middle', formatter:CodeGoods.rowFormatter},
                    ];
                };
                CodeGoods.formParams = function() {
                    var queryData = {};
                    queryData['offset'] = 0;
                    queryData['service'] = $("#service_filter").val();
                    // queryData['cost'] = $("#cost").val();
                    return queryData;
                }

                // CodeGoods.rowFormatter = function(value, row, index) {
                //     // Add the data-index attribute with the row id
                //     return `<tr data-indexx="${row.id}">` +
                //         `<td>${row.cost}</td>` +
                //         `</tr>`;
                // };
                CodeGoods.servicesFormatter = function(value,row,index) {

                    var html = `<div data-id=${row.id} class="service_name tooltip tooltip-right whitespace-normal text-left">`;
                    if(value && value.includes(' / ')){
                        var services = value.split(" / ");


                        // 遍历每一个服务名称，将其包裹在<p>标签内
                        services.forEach(function(service) {
                            html += '<p>' + service.trim() + '</p>';  // 去除多余空格
                        });
                    }else{
                        html += value;
                    }
                    html += '</div>';
                    return html;
                }
                CodeGoods.search = function() {
                    CodeGoods.table.refresh({query : CodeGoods.formParams()});
                };

                CodeGoods.reset = function() {
                    $("#searchGroup input,select").val('');
                    CodeGoods.table.refresh({query : CodeGoods.formParams()});
                };

                $(function() {
                    var defaultColunms = CodeGoods.initColumn();
                    var url = location.search;
                    var table = new BSTable(CodeGoods.id, Feng.ctxPath+"/dashboard/services/index"+url,defaultColunms,200);
                    // table.setPaginationType("server");
                    // table.setQueryParams(CodeGoods.formParams());
                    CodeGoods.table = table.init();
                    $(".fixed-table-toolbar,.fixed-table-pagination").remove();

                    $('#service_filter').on('input', function() {
                        var searchValue = $(this).val(); // 获取输入框中的值

                        // 动态设置查询参数
                        var queryParams = CodeGoods.formParams();
                        queryParams.name = searchValue; // 假设后端查询参数的字段名为 'name'

                        // 更新表格数据
                        table.refresh({query: queryParams});
                    });

                    $(document).on('click', '.service', function() {
                        const dataId = $(this).find('.service_name').data('id');
                        var ajax = new $ax(Feng.ctxPath + "/Dashboard/index/getPhone", function (data) {
                            console.log(data);
                            if (200 === data.status) {
                                Feng.success("Success" );
                                CodeHistory.table.refresh();
                            } else {
                                Feng.error("Failure+phone！" + data.msg + "！");
                            }
                        })
                        ajax.set('id', dataId);
                        ajax.start();

                    });
                });
            </script>
            

        </div>
    </div>

    <div class="flex flex-col p-8">
        <h2 class="card-title mb-2">Rented numbers</h2>

        <div class="text-stone-500">
            <p>No need to refresh the page to get the code.</p>
        </div>

        <div class="text-stone-500">
            You can get more SMS to the same number for free when 🔁 is displayed.
        </div>

        <div class="overflow-x-auto my-4">

            <?php if($isMoney): ?>
            <table class="table w-full">
                <!-- head -->
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Service</th>
                    <th>Phone</th>
                    <th>Code</th>
                    <th>Cost</th>
                    <th>TTL</th>
                    <th></th>
                </tr>
                </thead>

                <tbody id="rentals">
                <tr>
                    <td colspan="7" class="text-center">
                        <div>Please add money to your account to rent numbers.</div>
                        <div class="mt-4"><a href="/dashboard/wallet" class="btn btn-success">Open wallet</a></div>
                    </td>
                </tr>

                <template id="rental">
                    <tr>
                        <td class="id"></td>
                        <td class="service"></td>
                        <td class="phone font-mono"></td>
                        <td class="code font-mono"></td>
                        <td class="price"></td>
                        <td class="time_left font-mono"></td>

                        <td>
                            <a class="cancel cursor-pointer">🚫</a>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
            <?php else: ?>
                <table id="CodeHistoryTable" data-mobile-responsive="true" data-click-to-select="true" class="table w-full">
                    <thead><tr><th data-field="selectItem" data-checkbox="true"></th></tr></thead>
                </table>

                <script>
                    var CodeHistory = {id: "CodeHistoryTable",seItem: null,table: null,layerIndex: -1};

                    CodeHistory.initColumn = function () {
                        return [
                            // {field: 'selectItem', checkbox: false},
                            {title: 'id', field: 'id', visible: false, align: 'left', valign: 'middle',sortable: false},
                            {title: 'ID', field: 'phone_id', visible: true, align: 'left', valign: 'middle',sortable: false},
                            {title: 'Service', field: 'service_name', visible: true, align: 'left', valign: 'middle',sortable: false},
                            {title: 'Phone', field: 'phone', visible: true, align: 'left', valign: 'middle',sortable: false},
                            {title: 'Code', field: 'sms_code', visible: true, align: 'left', valign: 'middle',sortable: false},
                            {title: 'Cost', field: 'price', visible: true, align: 'left', valign: 'middle',sortable: false},
                            {title: 'TTL', field: 'status', visible: true, align: 'left', class:'do_status', valign: 'middle',sortable: false,formatter:CodeHistory.isactiveFormatter},
                        ];
                    };
                    CodeHistory.formParams = function() {
                        var queryData = {};
                        queryData['offset'] = 0;
                        queryData['service'] = $("#service_filter").val();
                        // queryData['cost'] = $("#cost").val();
                        return queryData;
                    }

                    CodeHistory.isactiveFormatter = function(value,row,index) {
                        if(value == 0){
                            value = `<span data-id=${row.id} class='isdoing'>🚫</span>`; //🚫🔁💀
                        }else if(value == 1 || value == 3){
                            value = "<span class='isdone'>✅</span>";
                        }
                        return value;
                    }
                    CodeHistory.refreshTable = function() {
                        // 这里是刷新表格的逻辑
                        CodeHistory.table.refresh({
                            url: Feng.ctxPath + "/dashboard/history/index" + location.search
                        });
                    };


                    $(function() {
                        var defaultColunms = CodeHistory.initColumn();
                        var url = location.search;
                        var table = new BSTable(CodeHistory.id, Feng.ctxPath+"/dashboard/history/index"+url,defaultColunms,200);
                        table.setPaginationType("server");
                        table.setQueryParams(CodeGoods.formParams());
                        CodeHistory.table = table.init();
                        $(".fixed-table-toolbar,.fixed-table-pagination").remove();

                        var intervalId = setInterval(function() {
                            if ($('.isdoing').length > 0) {
                                $('.isdoing').each(function() {
                                    const dataId = $(this).data('id');
                                    var ajax = new $ax(Feng.ctxPath + "/Dashboard/index/getCode", function (data) {
                                        if (200 === data.status) {
                                            Feng.success("Success" );
                                            CodeHistory.table.refresh();
                                        }
                                    })
                                    ajax.set('id', dataId);
                                    ajax.start();
                                });
                            } else {
                                // 如果不存在，可以选择停止请求
                                clearInterval(intervalId);
                                console.log('不再请求，未找到 isdoing 元素');
                            }
                        }, 3000);
                    });


                    $(document).on('click', '.isdoing', function() {
                        const historyId = $(this).data('id');
                        var ajax = new $ax(Feng.ctxPath + "/Dashboard/index/cancel", function (data) {
                            if (200 === data.status) {
                                Feng.success("Success" );
                                CodeHistory.table.refresh();
                            } else {
                                Feng.error("Failure！" + data.msg + "！");
                            }
                        })
                        ajax.set('id', historyId);
                        ajax.start();

                    });
                </script>

            <?php endif; ?>
        </div>
    </div>

    <template id="unlisted_notice">
        <div class="text-left inline-block">
            <div>Unlisted service will reserve the number for you.</div>
            <div>As soon as a new SMS comes, we'll give you the entire text.</div>
            <div>This will not work for listed services.</div>
            <div>We don't work with any banking or dating services.</div>

            <label class="cursor-pointer flex items-center text-sm mt-4">
                <input id="unlisted_notice_no_show" type="checkbox" class="checkbox mr-2" />
                <span>Don't show this message again</span>
            </label>
        </div>
    </template>

    <template id="first_use_notice">
        <div class="text-left inline-block">
            <div class="my-6">
                We recommend reading
                <a href="/docs/howto" class="link" target="_blank">this short guide</a>
                on how to successfully use this service.
            </div>

            <div class="my-6">Main takeaway: avoid using free VPN apps.</div>

            <label class="cursor-pointer flex items-center text-sm mt-4">
                <input id="first_use_notice_no_show" type="checkbox" class="checkbox mr-2" />
                <span>Don't show this message again</span>
            </label>
        </div>
    </template>

    <template id="cancel_notice">
        <p>Do you want to cancel this rental? Your balance will be unlocked.</p>
    </template>

    <div class="modal" id="carrier-info">
        <div class="modal-box w-1/2">
            <h3 class="font-bold text-lg">Why choose carrier</h3>
            <p class="py-4">There are 3 carriers in the USA: T-Mobile (tmo), Verizon (vz), and AT&T (att). </p>
            <p>If you're experiencing issues on services like Tinder, where it says an account already exists, we recommend you try Verizon, or AT&T.</p>
            <div class="modal-action">
                <a href="#" class="btn">OK</a>
            </div>
        </div>
    </div>
</div>

<!--左侧导航开始-->
<div class="bg-base-100 border-t">
    <footer class="footer py-20 px-10 w-full 2xl:w-1/2 mx-auto">
        <div>
            <span class="footer-title">Company</span>

            <a class="link link-hover" href="/docs/privacy">Privacy Policy</a>
            <a class="link link-hover" href="/docs/terms">Terms and Conditions</a>
        </div>
        <div>
            <span class="footer-title">Help</span>

            <a class="link link-hover" href="/dashboard/dynamic-pricing-info">Dynamic pricing</a>
            <a class="link link-hover" href="/docs/howto">How to register services online without bans</a>
            <a class="link link-hover" href="/docs/temporary-numbers-for-openai">Temporary numbers for OpenAI: An Easy Guide</a>
        </div>
        <div>
            <span class="footer-title">Contact Us</span>

            <span>
                <a class="link" onclick="$crisp.push(['do', 'chat:open'])">Online chat</a>
            </span>

            <span>Email: support@daisysms.com</span>

            <a class="link link-hover" href="/forms/report-fraud">Dispute / Fraud Inquiry</a>

            <span>Phone: 1 (808) 219-5564</span>
        </div>
    </footer>
</div>
<script src="/static/js/content.js?v=1.0.0"></script>

<style>
    .pagination{display:inline-block;padding-left:0;margin:20px 0;border-radius:4px}.pagination>li{display:inline}.pagination>li>a,.pagination>li>span{position:relative;float:left;padding:6px 12px;margin-left:-1px;line-height:1.42857143;color:#337ab7;text-decoration:none;background-color:#fff;border:1px solid #ddd}.pagination>li:first-child>a,.pagination>li:first-child>span{margin-left:0;border-top-left-radius:4px;border-bottom-left-radius:4px}.pagination>li:last-child>a,.pagination>li:last-child>span{border-top-right-radius:4px;border-bottom-right-radius:4px}.pagination>li>a:focus,.pagination>li>a:hover,.pagination>li>span:focus,.pagination>li>span:hover{z-index:2;color:#23527c;background-color:#eee;border-color:#ddd}.pagination>.active>a,.pagination>.active>a:focus,.pagination>.active>a:hover,.pagination>.active>span,.pagination>.active>span:focus,.pagination>.active>span:hover{z-index:3;color:#fff;cursor:default;background-color:#337ab7;border-color:#337ab7}.pagination>.disabled>a,.pagination>.disabled>a:focus,.pagination>.disabled>a:hover,.pagination>.disabled>span,.pagination>.disabled>span:focus,.pagination>.disabled>span:hover{color:#777;cursor:not-allowed;background-color:#fff;border-color:#ddd}.pagination-lg>li>a,.pagination-lg>li>span{padding:10px 16px;font-size:18px;line-height:1.3333333}.pagination-lg>li:first-child>a,.pagination-lg>li:first-child>span{border-top-left-radius:6px;border-bottom-left-radius:6px}.pagination-lg>li:last-child>a,.pagination-lg>li:last-child>span{border-top-right-radius:6px;border-bottom-right-radius:6px}.pagination-sm>li>a,.pagination-sm>li>span{padding:5px 10px;font-size:12px;line-height:1.5}.pagination-sm>li:first-child>a,.pagination-sm>li:first-child>span{border-top-left-radius:3px;border-bottom-left-radius:3px}.pagination-sm>li:last-child>a,.pagination-sm>li:last-child>span{border-top-right-radius:3px;border-bottom-right-radius:3px}.pager{padding-left:0;margin:20px 0;text-align:center;list-style:none}.pager li{display:inline}.pager li>a,.pager li>span{display:inline-block;padding:5px 14px;background-color:#fff;border:1px solid #ddd;border-radius:15px}.pager li>a:focus,.pager li>a:hover{text-decoration:none;background-color:#eee}.pager .next>a,.pager .next>span{float:right}.pager .previous>a,.pager .previous>span{float:left}.pager .disabled>a,.pager .disabled>a:focus,.pager .disabled>a:hover,.pager .disabled>span{color:#777;cursor:not-allowed;background-color:#fff}.label{display:inline;padding:.2em .6em .3em;font-size:75%;font-weight:700;line-height:1;color:#fff;text-align:left;white-space:nowrap;vertical-align:baseline;border-radius:.25em}a.label:focus,a.label:hover{color:#fff;text-decoration:none;cursor:pointer}
    .h9 {
        background-color: rgb(250, 229, 225);
    }
     .h8 {
         color: rgb(229, 57, 23);
     }
</style>

<!--左侧导航结束-->

<script src="https://unpkg.com/notie@4.3.1/dist/notie.min.js"></script>

<style>.do_status{cursor:pointer;}</style>
</body>