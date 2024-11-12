<?php

//接口路由文件

use think\facade\Route;
Route::rule('Member/delete', 'Member/delete')->middleware(['SmsAuth']);    //会员管理删除;
Route::rule('Reg/index', 'Reg/index')->middleware(['SmsAuth']);    //会员管理删除;
Route::rule('Member/updatePwd', 'Member/updatePwd')->middleware(['JwtAuth']);    //会员管理删除;
Route::rule('Member/exp', 'Member/exp')->middleware(['JwtAuth']);    //会员管理备注管理;
Route::rule('Member/modifyads', 'Member/modifyads')->middleware(['JwtAuth']);    //会员管理备注管理;
Route::rule('Member/ads', 'Member/ads')->middleware(['JwtAuth']);    //会员管理备注管理;

Route::rule('Member/chatlist', 'Member/chatlist')->middleware(['JwtAuth']);    //图片上传;

Route::rule('Message/lixian', 'Message/lixian')->middleware(['JwtAuth']);    //图片上传;
Route::rule('Message/lixianbyid', 'Message/lixianbyid')->middleware(['JwtAuth']);    //图片上传;
Route::rule('Member/view', 'Member/view')->middleware(['JwtAuth']);    //图片上传;

Route::rule('MemberTag/add', 'MemberTag/add')->middleware(['JwtAuth']);    //图片上传;
Route::rule('MemberTag/index', 'MemberTag/index')->middleware(['JwtAuth']);    //图片上传;
Route::rule('MemberTousu/add', 'MemberTousu/add')->middleware(['JwtAuth']);    //会员投诉;

Route::rule('FriendCircle/add', 'FriendCircle/add')->middleware(['JwtAuth']);    //发布朋友圈;

Route::rule('FriendCircle/interest', 'FriendCircle/interest')->middleware(['JwtAuth']);    //发布朋友圈;
Route::rule('FriendCircle/follow', 'FriendCircle/follow')->middleware(['JwtAuth']);    //发布朋友圈;
Route::rule('FriendCircle/discover', 'FriendCircle/discover')->middleware(['JwtAuth']);    //发布朋友圈;
Route::rule('FriendCircle/vote', 'FriendCircle/vote')->middleware(['JwtAuth']);    //发布朋友圈;
Route::rule('FriendCircle/like', 'FriendCircle/like')->middleware(['JwtAuth']);    //评论;
Route::rule('FriendComment/index', 'FriendComment/index')->middleware(['JwtAuth']);    //评论;

Route::rule('FriendCircle/comment', 'FriendCircle/comment')->middleware(['JwtAuth']);    //评论;


Route::rule('FriendCircle/view', 'FriendCircle/view')->middleware(['JwtAuth']);    //评论;
Route::rule('FriendCircle/owner', 'FriendCircle/owner')->middleware(['JwtAuth']);    //评论;

Route::rule('follow/add', 'follow/add')->middleware(['JwtAuth']);    //评论;
Route::rule('Fans/index', 'Fans/index')->middleware(['JwtAuth']);    //评论;
Route::rule('follow/index', 'follow/index')->middleware(['JwtAuth']);    //评论;
Route::rule('Message/sendAll', 'Message/sendAll')->middleware(['JwtAuth']);    //评论;
Route::rule('Message/check', 'Message/check')->middleware(['JwtAuth']);    //评论;
Route::rule('Message/noticesee', 'Message/noticesee')->middleware(['JwtAuth']);    //评论;
Route::rule('Friend/remark', 'Friend/remark')->middleware(['JwtAuth']);    //评论;
Route::rule('Friend/getByType', 'Friend/getByType')->middleware(['JwtAuth']);    //评论;

Route::rule('/Member/star', '/Member/star')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/Member/lists', '/Member/lists')->middleware(['JwtAuth']);    //图片上传;
Route::rule('Member/send', 'Member/send')->middleware(['JwtAuth']);    //评论;
Route::rule('FriendComment/like', 'FriendComment/like')->middleware(['JwtAuth']);    //评论;
Route::rule('Member/update', 'Member/update')->middleware(['JwtAuth']);    //评论;
Route::rule('Member/updatePhone', 'Member/updatePhone')->middleware(['JwtAuth', 'SmsAuth']);    //评论;
Route::rule('Login/phone', 'Login/phone')->middleware(['SmsAuth']);

Route::rule('FriendBoasting/index', 'FriendBoasting/index')->middleware(['JwtAuth']);
Route::rule('FriendBoasting/member', 'FriendBoasting/member')->middleware(['JwtAuth']);
Route::rule('FriendBoasting/like', 'FriendBoasting/like')->middleware(['JwtAuth']);
Route::rule('FriendBoasting/add', 'FriendBoasting/add')->middleware(['JwtAuth']);
Route::rule('FriendBoasting/isPraise', 'FriendBoasting/isPraise')->middleware(['JwtAuth']);

Route::rule('Sport/joingroup', 'Sport/joingroup')->middleware(['JwtAuth']);
Route::rule('Sport/outgroup', 'Sport/outgroup')->middleware(['JwtAuth']);
Route::rule('Sport/message', 'Sport/message')->middleware(['JwtAuth']);
Route::rule('FriendReport/add', 'FriendReport/add')->middleware(['JwtAuth']);
Route::rule('MemberSee/index', 'MemberSee/index')->middleware(['JwtAuth']);
Route::rule('MemberSee/owner', 'MemberSee/owner')->middleware(['JwtAuth']);
Route::rule('MemberSee/add', 'MemberSee/add')->middleware(['JwtAuth']);
Route::rule('FriendCircleTag/history', 'FriendCircleTag/history')->middleware(['JwtAuth']);
Route::rule('AddressList/add', 'AddressList/add')->middleware(['JwtAuth']);
Route::rule('Friend/get', 'Friend/get')->middleware(['JwtAuth']);
Route::rule('Friend/delete', 'Friend/delete')->middleware(['JwtAuth']);
Route::rule('Friend/update_relation', 'Friend/update_relation')->middleware(['JwtAuth']);
Route::rule('Room/add', 'Room/add')->middleware(['JwtAuth']);
Route::rule('Message/index', 'Message/index')->middleware(['JwtAuth']);
Route::rule('FriendCircle/delete', 'FriendCircle/delete')->middleware(['JwtAuth']);
Route::rule('FriendCircle/top', 'FriendCircle/top')->middleware(['JwtAuth']);

Route::rule('Kf/message', 'Kf/message')->middleware(['JwtAuth']);

Route::rule('/CustomerConsultation/add', '/CustomerConsultation/add')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/view', '/CustomerConsultation/view')->middleware(['JwtAuth']);    //图片上传;


#接单大厅
Route::rule('/CustomerConsultation/index', '/CustomerConsultation/index')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/info', '/CustomerConsultation/info')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/receiving_orders', '/CustomerConsultation/receiving_orders')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/join_group', '/CustomerConsultation/join_group')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/message_list', '/CustomerConsultation/message_list')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/message_info', '/CustomerConsultation/message_info')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/send_message', '/CustomerConsultation/send_message')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/updateMessageStatus', '/CustomerConsultation/updateMessageStatus')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/Member/save_bg_img', '/Member/save_bg_img')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/Member/save_avatar_img', '/Member/save_avatar_img')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/Member/set_member_like', '/Member/set_member_like')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/Member/set_member_un_like', '/Member/set_member_un_like')->middleware(['JwtAuth']);    //图片上传;

Route::rule('/CustomerConsultation/set_read', '/CustomerConsultation/set_read')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/CustomerConsultation/read_num', '/CustomerConsultation/read_num')->middleware(['JwtAuth']);    //图片上传;
Route::rule('/InterfaceError/add', '/InterfaceError/add')->middleware(['JwtAuth']);    //告警;


Route::rule('/NewFriendCircle/interest', '/NewFriendCircle/interest')->middleware(['JwtAuth']);    //新版朋友圈
Route::rule('/NewFriendCircle/my_circle', '/NewFriendCircle/my_circle')->middleware(['JwtAuth']);    //新版朋友圈
Route::rule('/NewFriendCircle/circle_like_list', '/NewFriendCircle/circle_like_list')->middleware(['JwtAuth']);    //新版朋友圈


Route::rule('Groups/nickname', 'Groups/nickname')->middleware(['JwtAuth']);    //修改群成员昵称
Route::rule('Groups/remark', 'Groups/remark')->middleware(['JwtAuth']);    //修改群备注
Route::rule('Groups/quit', 'Groups/quit')->middleware(['JwtAuth']);    //解散群
Route::rule('Groups/member', 'Groups/member')->middleware(['JwtAuth']);    //群主成员
Route::rule('Groups/get', 'Groups/get')->middleware(['JwtAuth']);    //获取群信息
Route::rule('Groups/add', 'Groups/add')->middleware(['JwtAuth']);    //添加群
Route::rule('Groups/info', 'Groups/info')->middleware(['JwtAuth']);    //群信息
Route::rule('Groups/create_qrcode', 'Groups/create_qrcode')->middleware(['JwtAuth']);    //群信息
Route::rule('Groups/group_add_by_qrcode', 'Groups/group_add_by_qrcode')->middleware(['JwtAuth']);    //群信息
Route::rule('Groups/set_group_remark', 'Groups/set_group_remark')->middleware(['JwtAuth']);    //修改群备注
Route::rule('Groups/leave_group', 'Groups/leave_group')->middleware(['JwtAuth']);    //退出群
Route::rule('Groups/set_state', 'Groups/set_state')->middleware(['JwtAuth']);    //设置群是否加入通讯录
Route::rule('Groups/stated_group', 'Groups/stated_group')->middleware(['JwtAuth']);    //设置群是否加入通讯录


Route::rule('Member/create_qrcode', 'Member/create_qrcode')->middleware(['JwtAuth']);    //群信息

Route::rule('Groups/rename', 'Groups/rename')->middleware(['JwtAuth']);    //群信息

#好友申请
Route::rule('/FriendApplication/index', '/FriendApplication/index')->middleware(['JwtAuth']);    //群信息
Route::rule('/FriendApplication/add', '/FriendApplication/add')->middleware(['JwtAuth']);    //群信息
Route::rule('/FriendApplication/access', '/FriendApplication/access')->middleware(['JwtAuth']);    //群信息
Route::rule('/FriendApplication/delete', '/FriendApplication/delete')->middleware(['JwtAuth']);    //群信息
Route::rule('/FriendApplication/report', '/FriendApplication/report')->middleware(['JwtAuth']);    //群信息
Route::rule('/FriendApplication/search', '/FriendApplication/search')->middleware(['JwtAuth']);    //好友搜索


Route::rule('/FriendTagManage/index', '/FriendTagManage/index')->middleware(['JwtAuth']);    //好友标签管理
Route::rule('/FriendTagManage/add', '/FriendTagManage/add')->middleware(['JwtAuth']);    //好友标签修改
Route::rule('/FriendTagManage/delete', '/FriendTagManage/delete')->middleware(['JwtAuth']);    //删除标签
Route::rule('/FriendTagManage/detail', '/FriendTagManage/detail')->middleware(['JwtAuth']);    //删除标签
#朋友圈接口
Route::rule('/Moment/publish', '/Moment/publish')->middleware(['JwtAuth']);    //发布朋友圈
Route::rule('/Moment/lists', '/Moment/lists')->middleware(['JwtAuth']);    //查看朋友圈
Route::rule('/Moment/detail', '/Moment/detail')->middleware(['JwtAuth']);    //获取朋友圈单条
Route::rule('/Moment/like', '/Moment/like')->middleware(['JwtAuth']);    //朋友圈点赞
Route::rule('/Moment/comment', '/Moment/comment')->middleware(['JwtAuth']);    //朋友圈评论
Route::rule('/Moment/my_circle', '/Moment/my_circle')->middleware(['JwtAuth']);    //查看具体某个人朋友圈列表
Route::rule('/Moment/deleteComment', '/Moment/deleteComment')->middleware(['JwtAuth']);    //删除朋友圈评论
Route::rule('/Moment/pictures', '/Moment/pictures')->middleware(['JwtAuth']);    //图片

Route::rule('/MemberFindMethod/setting', '/MemberFindMethod/setting')->middleware(['JwtAuth']);    //图片
Route::rule('/MemberFindMethod/noSeeMe', '/MemberFindMethod/noSeeMe')->middleware(['JwtAuth']);    //图片
Route::rule('/MemberFindMethod/noSeeHim', '/MemberFindMethod/noSeeHim')->middleware(['JwtAuth']);    //图片
Route::rule('/Moment/delete', '/Moment/delete')->middleware(['JwtAuth']);    //图片


Route::rule('/MemberCollect/type', '/MemberCollect/type')->middleware(['JwtAuth']);    //图片
Route::rule('/MemberCollect/lists', '/MemberCollect/lists')->middleware(['JwtAuth']);    //图片
Route::rule('/MemberCollect/message', '/MemberCollect/message')->middleware(['JwtAuth']);    //图片
Route::rule('/MemberCollect/collect', '/MemberCollect/collect')->middleware(['JwtAuth']);    //图片
Route::rule('/MemberCollect/addChat', '/MemberCollect/addChat')->middleware(['JwtAuth']);    //图片
Route::rule('/Moment/friendPic', '/Moment/friendPic')->middleware(['JwtAuth']);    //图片

Route::rule('/Moment/MomentNotice', '/Moment/MomentNotice')->middleware(['JwtAuth']);    //图片
Route::rule('/FriendApplication/noticeCount', '/FriendApplication/noticeCount')->middleware(['JwtAuth']);    //图片

Route::rule('/Video/index', '/Video/index')->middleware(['JwtAuth']);    //图片
Route::rule('/Video/see', '/Video/see')->middleware(['JwtAuth']);    //图片

Route::rule('/Moment/MomentNoticeClear', '/Moment/MomentNoticeClear')->middleware(['JwtAuth']);    //图片
Route::rule('/Moment/DeleteMomentNotice', '/Moment/DeleteMomentNotice')->middleware(['JwtAuth']);    //图片
Route::rule('/Moment/my_video', '/Moment/my_video')->middleware(['JwtAuth']);    //图片

Route::rule('/Groups/transferGroup', '/Groups/transferGroup')->middleware(['JwtAuth']);    //图片

Route::rule('/Video/zan', '/Video/zan')->middleware(['JwtAuth']);    //图片
Route::rule('/Video/comment', '/Video/comment')->middleware(['JwtAuth']);    //图片
Route::rule('/Groups/delete_group_member', '/Groups/delete_group_member')->middleware(['JwtAuth']);    //图片
Route::rule('/Member/exchange', '/Member/exchange')->middleware(['JwtAuth']);    //兑换

//BinanceAPI

Route::rule('/BinanceApi/getUserKey', '/BinanceApi/getUserKey')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getTradeUserConfig', '/BinanceApi/getTradeUserConfig')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/saveUserKey', '/BinanceApi/saveUserKey')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getLeaderConfigByUserId', '/BinanceApi/getLeaderConfigByUserId')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getLeaderConfigByUserIdNewindex', '/BinanceApi/getLeaderConfigByUserIdNewindex')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/saveTradeUserConfig', '/BinanceApi/saveTradeUserConfig')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getcoinList', '/BinanceApi/getcoinList')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getJyslist', '/BinanceApi/getJyslist')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getLeaderConfigByUserIdCatidindex', '/BinanceApi/getLeaderConfigByUserIdCatidindex')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getLeaderConfigOrderByLeaderId', '/BinanceApi/getLeaderConfigOrderByLeaderId')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getUserStatMain', '/BinanceApi/getUserStatMain')->middleware(['JwtAuth']);    //兑换
//Route::rule('/BinanceApi/getLeaderConfig', '/BinanceApi/getLeaderConfig')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getCatlist', '/BinanceApi/getCatlist')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getAllconfig', '/BinanceApi/getAllconfig')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getLeaderConfigByUserSet', '/BinanceApi/getLeaderConfigByUserSet')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getLeaderConfigByLeaderId', '/BinanceApi/getLeaderConfigByLeaderId')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/test6', '/BinanceApi/test6')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/getUserOrderlist', '/BinanceApi/getUserOrderlist')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceApi/transferHistory', '/BinanceApi/transferHistory')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getLeaderPos', '/BinanceotherApi/getLeaderPos')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getMyOrder', '/BinanceotherApi/getMyOrder')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getUserPnl', '/BinanceotherApi/getUserPnl')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getUserAccount', '/BinanceotherApi/getUserAccount')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getUserPnlByDay', '/BinanceotherApi/getUserPnlByDay')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getLeaderConfigByUserSet', '/BinanceotherApi/getLeaderConfigByUserSet')->middleware(['JwtAuth']);    //兑换
Route::rule('/BinanceotherApi/getleaderTradeData', '/BinanceotherApi/getleaderTradeData')->middleware(['JwtAuth']);    //兑换
//会员相关
Route::rule('MymemberApi/info', 'MymemberApi/info')->middleware(['JwtAuth']);
Route::rule('MymemberApi/checkIncome', 'MymemberApi/checkIncome')->middleware(['JwtAuth']);
Route::rule('MymemberApi/Getincomeday', 'MymemberApi/Getincomeday')->middleware(['JwtAuth']);
Route::rule('MymemberApi/Getbill', 'MymemberApi/Getbill')->middleware(['JwtAuth']);
Route::rule('MymemberApi/GetMyDownbill', 'MymemberApi/GetMyDownbill')->middleware(['JwtAuth']);
Route::rule('MymemberApi/GetMyTgmember', 'MymemberApi/GetMyTgmember')->middleware(['JwtAuth']);
Route::rule('MymemberApi/GetMyTgDownmember', 'MymemberApi/GetMyTgDownmember')->middleware(['JwtAuth']);
Route::rule('MymemberApi/GetMsg', 'MymemberApi/GetMsg')->middleware(['JwtAuth']);
Route::rule('MymemberApi/GetMonylog', 'MymemberApi/GetMonylog')->middleware(['JwtAuth']);
Route::rule('MymemberApi/GetMonyloginfo', 'MymemberApi/GetMonyloginfo')->middleware(['JwtAuth']);
Route::rule('MymemberApi/withdrawalList', 'MymemberApi/withdrawalList')->middleware(['JwtAuth']);
Route::rule('MymemberApi/syinfo', 'MymemberApi/syinfo')->middleware(['JwtAuth']);
Route::rule('MymemberApi/getCountInfoLevel', 'MymemberApi/getCountInfoLevel')->middleware(['JwtAuth']);
Route::rule('MymemberApi/upaupay', 'MymemberApi/upaupay')->middleware(['JwtAuth']);
Route::rule('MymemberApi/gettxmsg', 'MymemberApi/gettxmsg')->middleware(['JwtAuth']);
Route::rule('MymemberApi/getTg', 'MymemberApi/getTg')->middleware(['JwtAuth']);
Route::rule('MymemberApi/getsummsg', 'MymemberApi/getsummsg')->middleware(['JwtAuth']);
Route::rule('MymemberApi/msgList', 'MymemberApi/msgList')->middleware(['JwtAuth']);
//支付相关
Route::rule('Charge/payOrder', 'Charge/payOrder')->middleware(['JwtAuth']);
Route::rule('Charge/getPaylist', 'Charge/getPaylist')->middleware(['JwtAuth']);
Route::rule('Charge/checkIncome', 'Charge/checkIncome')->middleware(['JwtAuth']);
Route::rule('Charge/getProductlist', 'Charge/getProductlist')->middleware(['JwtAuth']);
Route::rule('Charge/buyProduct', 'Charge/buyProduct')->middleware(['JwtAuth']);
Route::rule('Charge/addOrder', 'Charge/addOrder')->middleware(['JwtAuth']);
Route::rule('Charge/doChangeMoney', 'Charge/doChangeMoney')->middleware(['JwtAuth']);

//代理
Route::rule('AgentApi/getUserAccount', 'AgentApi/getUserAccount')->middleware(['JwtAuth']);
Route::rule('AgentApi/getCountInfoLevel', 'AgentApi/getCountInfoLevel')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetMonylog', 'AgentApi/GetMonylog')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetMsg', 'AgentApi/GetMsg')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetMyTgmember', 'AgentApi/GetMyTgmember')->middleware(['JwtAuth']);
Route::rule('AgentApi/getApilist', 'AgentApi/getApilist')->middleware(['JwtAuth']);
Route::rule('AgentApi/getUpApitime', 'AgentApi/getUpApitime')->middleware(['JwtAuth']);
Route::rule('AgentApi/getUpApistatus', 'AgentApi/getUpApistatus')->middleware(['JwtAuth']);
Route::rule('AgentApi/getUserOrderlist', 'AgentApi/getUserOrderlist')->middleware(['JwtAuth']);
Route::rule('AgentApi/pcAct', 'AgentApi/pcAct')->middleware(['JwtAuth']);
Route::rule('AgentApi/pcBuyuser', 'AgentApi/pcBuyuser')->middleware(['JwtAuth']);
Route::rule('AgentApi/doQdToAccount', 'AgentApi/doQdToAccount')->middleware(['JwtAuth']);
Route::rule('AgentApi/doQdTouser', 'AgentApi/doQdTouser')->middleware(['JwtAuth']);
Route::rule('AgentApi/info', 'AgentApi/info')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetMyDownCzList', 'AgentApi/GetMyDownCzList')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetMyTgChannel', 'AgentApi/GetMyTgChannel')->middleware(['JwtAuth']);
Route::rule('AgentApi/getChargelist', 'AgentApi/getChargelist')->middleware(['JwtAuth']);
Route::rule('AgentApi/getWithdrawallist', 'AgentApi/getWithdrawallist')->middleware(['JwtAuth']);
Route::rule('AgentApi/doChargelist', 'AgentApi/doChargelist')->middleware(['JwtAuth']);
Route::rule('AgentApi/doAdChargelist', 'AgentApi/doAdChargelist')->middleware(['JwtAuth']);
Route::rule('AgentApi/doWithdrawallist', 'AgentApi/doWithdrawallist')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetAgentDaybill', 'AgentApi/GetAgentDaybill')->middleware(['JwtAuth']);
Route::rule('AgentApi/GetAgentMyDownbill', 'AgentApi/GetAgentMyDownbill')->middleware(['JwtAuth']);

Route::rule('/BinancemartingaleApi/getCoinInfoList', '/BinancemartingaleApi/getCoinInfoList')->middleware(['JwtAuth']);    //兑换

Route::rule('Aboutus/getcustomerinfo', 'Aboutus/getcustomerinfo')->middleware(['JwtAuth']);
Route::rule('Aboutus/Adcustomer', 'Aboutus/Adcustomer')->middleware(['JwtAuth']);
Route::rule('Aboutus/customerList', 'Aboutus/customerList')->middleware(['JwtAuth']);
Route::rule('AgentApi/doUpremark','AgentApi/doUpremark')->middleware(['JwtAuth']);

Route::rule('Charge/getPayInfo', 'Charge/getPayInfo')->middleware(['JwtAuth']);    //评论;