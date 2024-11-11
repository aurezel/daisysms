<?php /*a:4:{s:66:"D:\phpEnv\www\github\daisysms\app\dashboard\view\wallet\index.html";i:1731314996;s:66:"D:\phpEnv\www\github\daisysms\app\dashboard\view\common\__top.html";i:1731296062;s:67:"D:\phpEnv\www\github\daisysms\app\dashboard\view\common\__left.html";i:1731296062;s:69:"D:\phpEnv\www\github\daisysms\app\dashboard\view\common\__footer.html";i:1731296062;}*/ ?>
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

    <div class="flex w-full justify-center bg-base-200">
        <div class="flex flex-col gap-6 my-6 w-full lg:w-3/4 w-3/4 2xl:w-1/2">
            <div class="flex flex-row gap-2">
                <div class="bg-success text-accent-content rounded-box flex items-center p-4 border">
                    <div class="flex-1 px-2">
                        <h2 class="text-3xl font-extrabold">$<?php echo $umoney; ?></h2>
                        <p class="text-sm text-opacity-80">Current balance</p>
                    </div>
                </div>

            </div>

            <div class="grid" id="tabs">
                <div class="tabs -mb-px z-10">
                    <a data-for="deposit" class="tab tab-lg tab-lifted tab-active">
                        Credit
                        <span class="hidden md:inline">&nbsp;money</span>
                    </a>

                    <a data-for="transfer-tab" class="tab tab-lg tab-lifted">Transfer</a>

                    <a data-for="redeem-tab" class="tab tab-lg tab-lifted">
                        Redeem
                        <span class="hidden md:inline">&nbsp;promocode</span>
                    </a>
                </div>

                <div class="bg-base-100 rounded-b-box rounded-tr-box relative overflow-x-auto">
                    <div data-tab="deposit" class="border card-body">
                        <form id="pay" action="javascript:void(0);" method="post" data-gtm-form-interact-id="0">
                            <h2 class="card-title mb-2">Add money to your wallet</h2>

                            <div class="flex flex-col sm:flex-row items-center">
                                <label class="input-group w-72">
                                    <span>Amount,&nbsp;USD</span>
                                    <input id="amount" name="amount" type="number" min="20" max="150" step="10" class="input input-bordered w-full" placeholder="100" required="" autofocus="">
                                </label>
                            </div>

                            <div class="mt-4 mb-2 text-stone-500">Payment method:</div>

                            <div id="methods" class="flex flex-col gap-1 select-none">









                                <div class="grid grid-cols-3 xl:grid-cols-4 gap-1">
                                    <div class="border rounded">
                                        <input id="USDT-TRON" type="radio" value="USDT-TRON" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDT-TRON">
                                            USD 20.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="TRX-TRON" type="radio" value="TRX-TRON" class="peer hidden" name="method" data-crypto="true" data-gtm-form-interact-field-id="0">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="TRX-TRON">
                                            USD 30.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="BTC-BTC" type="radio" value="BTC-BTC" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="BTC-BTC">
                                            USD 40.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="LTC-LTC" type="radio" value="LTC-LTC" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="LTC-LTC">
                                            USD 50.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="USDT-POLYGON" type="radio" value="USDT-POLYGON" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDT-POLYGON">
                                            USD 60.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="DOGE-DOGE" type="radio" value="DOGE-DOGE" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="DOGE-DOGE">
                                            USD 70.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="ETH-ETH" type="radio" value="ETH-ETH" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="ETH-ETH">
                                            USD 80.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="USDC-ETH" type="radio" value="USDC-ETH" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDC-ETH">
                                            USD 90.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="USDC-TRON" type="radio" value="USDC-TRON" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDC-TRON">
                                            USD 100.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="BCH-BCH" type="radio" value="BCH-BCH" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="BCH-BCH">
                                            USD 110.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="XMR-XMR" type="radio" value="XMR-XMR" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="XMR-XMR">
                                            USD 120.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="TON-TON" type="radio" value="TON-TON" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="TON-TON">
                                            USD 130.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="USDT-TON" type="radio" value="USDT-TON" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDT-TON">
                                            USD 140.00
                                        </label>
                                    </div>
                                    <div class="border rounded">
                                        <input id="USDT-BSC" type="radio" value="USDT-BSC" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDT-BSC">
                                            USD 150.00
                                        </label>
                                    </div>
                                    <div class="border rounded hidden">
                                        <input id="SOL-SOL" type="radio" value="SOL-SOL" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="SOL-SOL">
                                            USD 20.00
                                        </label>
                                    </div>
                                    <div class="border rounded hidden">
                                        <input id="USDT-ETH" type="radio" value="USDT-ETH" class="peer hidden" name="method" data-crypto="true">

                                        <label class="block p-4 cursor-pointer rounded peer-checked:ring-2 peer-checked:ring-emerald-400" for="USDT-ETH">
                                            USDT ETH
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button id="btn-credit" class="btn btn-success gap-2 mt-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                Proceed to Payment
                            </button>

                            <div class="text-stone-500 mt-4">
                                <p>Money is only used if you receive the SMS code. Please contact support to add a payment method.</p>
                            </div>

                            <div id="confirmation_modal" class="modal">
                                <div class="modal-box w-3/4 lg:w-1/2 xl:w-1/3 max-w-none">
                                    <div id="response-container" class="flex flex-col gap-4"></div>

                                    <p class="mt-4">
                                        By using this payment method you agree to
                                        <a class="link" target="_blank" href="/docs/card-payment-info">Card Payment Rules</a>
                                    </p>

                                    <div class="modal-action mt-8">
                                        <label class="btn btn-success accept">Accept</label>
                                        <label class="btn close">Close</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="transfer" data-tab="transfer-tab" class="border card-body hidden">
                        <h2 class="card-title mb-2">Transfer money to a different account</h2>

                        <span>Balance transfers are currently disabled for your account.</span>
                        <span>You can enable them by pressing the button below.</span>
                        <span>We recommend that you don't enable this feature unless you plan to use it.</span>

                        <button id="enable_transfers" class="btn btn-success gap-2 mt-4 py-2 h-auto">
                            <span>Enable balance transfers</span>
                        </button>
                    </div>

                    <div id="redeem" data-tab="redeem-tab" class="border card-body hidden">
                        <h2 class="card-title mb-2">Redeem promocode</h2>

                        <div class="flex flex-row gap-4">
                            <label class="input-group ">
                                <span>Promocode</span>
                                <input name="promocode" type="text" class="input input-bordered w-full" required="">
                            </label>
                        </div>

                        <button class="btn btn-success gap-2 mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right-circle"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                            Redeem promocode
                        </button>
                    </div>
                </div>
            </div>
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
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">External Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="modalIframe" src="" width="100%" height="500px" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<button type="button" onclick="CodeGoods.update(4293)" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
<script>
    var CodeGoods = {id: "CodeGoodsTable",seItem: null,table: null,layerIndex: -1};
    CodeGoods.update = function (value) {
        if(value){  console.log(Feng.ctxPath);
            // var index = layer.open({type: 2,title: '修改',area: ['800px', '400px'],fix: false, maxmin: true,content: Feng.ctxPath + '/dashboard/wallet/charge?id='+value});
            var index = layer.open({type: 2,title: '修改',area: ['800px', '400px'],fix: false, maxmin: true,content:  Feng.ctxPath + '/dashboard/wallet/testx?id='+value});
            // var index = layer.open({type: 2,title: '修改',area: ['800px', '400px'],fix: false, maxmin: true,content:   +'http://baidu.com/?id='+value});
            if(!IsPC()){layer.full(index)}
        }else{
            if (this.check()) {
                var idx = '';
                $.each(CodeGoods.seItem, function() {
                    idx += ',' + this.id;
                });
                idx = idx.substr(1);
                if(idx.indexOf(",") !== -1){
                    Feng.info("请选择单条数据！");
                    return false;
                }
                var index = layer.open({type: 2,title: '修改',area: ['800px', '400px'],fix: false, maxmin: true,content: Feng.ctxPath + '/MemberYzm/update?id='+idx});
                this.layerIndex = index;
                if(!IsPC()){layer.full(index)}
            }
        }
    }

    $(document).ready(function() {
        $('.tab').on('click', function() {
            // 移除所有标签的活动状态
            $('.tab').removeClass('tab-active');

            // 添加活动状态到当前标签
            $(this).addClass('tab-active');

            // 获取要显示的标签对应的内容
            const tabId = $(this).data('for');
            console.log(tabId)
            // 隐藏所有内容
            $('.border.card-body').addClass('hidden');

            // 显示对应的内容
            $(`div[data-tab="${tabId}"]`).removeClass('hidden');
        });

        $('input[name="method"]').on('change', function() {
            if ($(this).is(':checked')) {
                // 获取选中的 radio 对应的 label 的文本内容
                const selectedLabel = $(`label[for="${$(this).attr('id')}"]`).text().trim();
                let match = selectedLabel.match(/(\d+\.\d+)/);
                if (match) {
                    var amount = parseFloat(match[0]);
                    $('#amount').val(amount);
                    console.log(amount);  // 输出: 60.00
                }

            }
        });
        //  var amount = $('#amount').val();
        // console.log('t',amount)
        $('#btn-credit').on('click', function() {
            let isAmount = $('#amount').val();
            if(isAmount == ''){
                return;
            }
            var url = Feng.ctxPath + "/Dashboard/wallet/charge"; // 你想打开的页面 URL
            window.open(url, '_blank'); // _blank 表示在新标签页中打开
            // var ajax = new $ax(Feng.ctxPath + "/Dashboard/wallet/index", function (data) {
            //     console.log(data)
            //     openModalWithIframe("http://www.baidu.com");
            //     // if ('00' === data.status) {
            //     //     Feng.success(data.msg);
            //     //     CodeGoods.table.refresh();
            //     // } else {
            //     //     // Feng.error(data.msg);
            //     // }
            // });
            // ajax.set('amount', isAmount);
            // ajax.start();

        });
    });
    function openModalWithIframe(url) {
        $('#modalIframe').attr('src', url);  // 设置 iframe 的 src 属性
        $('#myModal').modal('show');  // 打开模态框
    }
</script>
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


</body>