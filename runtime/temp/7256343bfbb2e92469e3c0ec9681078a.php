<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:82:"E:\wwwroot\2025\git\xyh_xing\public/../application/admin\view\dashboard\index.html";i:1784712257;s:71:"E:\wwwroot\2025\git\xyh_xing\application\admin\view\layout\default.html";i:1769759345;s:68:"E:\wwwroot\2025\git\xyh_xing\application\admin\view\common\meta.html";i:1769759345;s:70:"E:\wwwroot\2025\git\xyh_xing\application\admin\view\common\script.html";i:1769759345;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config ?? ''); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <!--
 * @Author: Xyhao
 * @Date: 2025-10-10 09:14:35
 * @Description: 安徽爱喜网络科技有限公司
 -->
<style>
    body.darktheme .panel-statistics h4 {
        color: #ccc;
    }

    .panel-statistics h4 {
        color: #444;
        font-weight: bold;
        font-size: 14px;
    }

    .panel-statistics h3 {
        font-weight: 500;
        font-size: 14px;
        color: #333;
    }

    .panel-statistics .statistics-value {
        font-size: 14px;
        color: #666;
    }

    .panel-statistics em {
        font-style: normal;
    }

    .panel-statistics .pull-right {
        padding-right: 10px;
    }

    .panel-statistics .table thead tr th {
        font-weight: normal;
    }

    .panel-statistics .table tbody tr td {
        font-weight: normal;
        vertical-align: middle;
    }

    .panel-statistics .table tbody tr td p {
        margin: 0;
    }

    #echarts1 textarea {
        display: block;
    }

    select.model_id {
        min-width: 60px;
    }
</style>

<div class="row">
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日入金订单</h4>
                    <h3><?php echo $today_rujin_total; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="iconfont icon-rujin fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日入金总额(USDT)</h4>
                    <h3><?php echo $today_rujin_user_usdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#ffc8c8;">
                    <i class="iconfont icon-rujin2 fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日入金总额(CNY)</h4>
                    <h3><?php echo $today_rujin_total_money; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8e3ff;">
                    <i class="iconfont icon-tixian2 fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日入金返佣(USDT)</h4>
                    <h3>0.00</h3>
                </div>

                <div class="pull-right" style="color:#ffe9c8;">
                     <i class="iconfont icon-tixian2 fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日入金汇率差+手续费</h4>
                    <h3><?php echo $today_rujin_profit; ?></h3>
                </div>

                <div class="pull-right" style="color:#ffe9c8;">
                    <i class="iconfont icon-shouxufei3 fa-4x"></i>
                </div>
            </div>
        </div>
    </div>    
</div>

<div class="row" style="margin-top: 10px;">
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日出金订单数量</h4>
                    <h3><?php echo $today_chujin_total; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="iconfont icon-chujin fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日出金总额(USDT)</h4>
                    <h3><?php echo $today_chujin_user_usdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#ffc8c8;">
                     <i class="iconfont icon-fanyong fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日出金总额(CNY)</h4>
                    <h3><?php echo $today_chujin_total_money; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8e3ff;">
                     <i class="iconfont icon-ziyuan fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日出金返佣(USDT)</h4>
                    <h3>0.00</h3>
                </div>

                <div class="pull-right" style="color:#ffe9c8;">
                     <i class="iconfont icon-rujin1 fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日出金汇率差+手续费</h4>
                    <h3><?php echo $today_chujin_profit; ?></h3>
                </div>

                <div class="pull-right" style="color:#ffe9c8;">
                     <i class="iconfont icon-shouxufei1 fa-4x"></i>
                </div>
            </div>
        </div>
    </div>    
</div>

<div class="row" style="margin:15px 0;">

    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>商户余额</h4>
                    <h3><?php echo $supplyUsdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>商户已提现余额(USDT)</h4>
                    <h3><?php echo $total_supply_number; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>商户充值总额(USDT)</h4>
                    <h3><?php echo $supply_chongzhi; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>    
      <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>商户入金余额(USDT)</h4>
                    <h3><?php echo $rujin_supply_usdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>商户出金余额(USDT)</h4>
                    <h3><?php echo $cj_supply_usdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>    
</div>



<div class="row" style="margin:15px 0;">

    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>承兑商总额</h4>
                    <h3><?php echo $userUsdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>承兑商已提现总额(USDT)</h4>
                    <h3>0</h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>承兑商充值总额(USDT)</h4>
                    <h3><?php echo $user_chongzhi_number; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>    
      <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>承兑商入金总额(USDT)</h4>
                    <h3><?php echo $rj_user_usdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xs-6 col-sm-2">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>承兑商出金总额(USDT)</h4>
                    <h3><?php echo $cj_user_usdt; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>    
</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version'] ?? ''); ?>"></script>
    </body>
</html>
