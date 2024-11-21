
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/static/js/plugins/layui/css/layui.css">
    <title>实用工具</title>
    <style>
        .copy-text {
            cursor: pointer;
            user-select: none;
        }

        .copy-success {
            color: green;
        }
        .center-container {
            width: 800px;
            margin: 0 auto;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="center-container">
    <h1 class="page-title" style="margin-top:60px;text-align:center;">数据处理</h1>
    <div class="layui-form-item layui-form-text">
        <label class="layui-form-labelx">消息显示：</label>
        <div class="layui-input-blockx">
            <div class="content50 copy-text" style="color:#16b777;" id="displayInfo" ondblclick="copyText(50)">

                <?php $filePath = "source.log";

                // 读取文件内容
                $logRecords = @file_get_contents($filePath);
                $logRecords = explode('[----------]', $logRecords);
                $recentLogs = array_slice(array_reverse($logRecords), 0, 5);
                // 输出文件内容
                if(!empty(trim($recentLogs[0]))){
                    $fileContent = explode('##@##', $recentLogs[0]);
                    if(count($fileContent)>1){
                        echo $fileContent[1];
                    }

                }else{
                    if(!empty($recentLogs[1])){
                        $fileContent = explode('##@##', $recentLogs[1]);
                        if(count($fileContent)>1){
                            echo $fileContent[1];
                        }
                    }

                }
                ?></div>
        </div>
    </div>
    <div style="margin-bottom:500px;">
        <form id="myForm" action="#" method="post">
            <label for="textInput">输入文本：</label>
            <textarea id="textInput" name="textInput" class="layui-textarea" ondblclick="copyAndShowSuccess(this)" name="user_text" rows="15" cols="40" placeholder="请输入内容"></textarea>
            <button class="layui-btn" style="margin-top:10px;"  type="submit">提交</button>
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // 获取表单提交的文本
            // 将文本写入文件
            if(!empty(trim($_POST["textInput"]))){
                $file = $filePath;
                if (count($logRecords) > 9) {
                    $inLogRecords = array_slice($logRecords, -9);
                    $inLogRecords[] = date("Y-m-d H:i:s") . "##@##" . $_POST["textInput"];
                    $inLogRecords = implode("[----------]", $inLogRecords);
                    file_put_contents($file, $inLogRecords . PHP_EOL);
                }else{
                    $inLogRecords = date("Y-m-d H:i:s") . "##@##" . $_POST["textInput"];
                    $inLogRecords .= "[----------]";
                    file_put_contents($file, $inLogRecords . PHP_EOL,FILE_APPEND);
                }

                // 返回响应
                echo "表单提交成功！文本已写入文件。";
            }
            header("Location: m.php");
        }
        ?>
    </div>
    <?php if(count($recentLogs)>1){
        foreach($recentLogs as $key => $recent){
            $msg = explode("##@##",$recent);
            if(count($msg)>1){
                ?>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-labelx">时间:<?php echo $msg[0];?></label>
                    <div class="layui-input-blockx">
                        <div class="content<?php echo $key;?> copy-text" style="color:#16b777;"  ondblclick="copyText(<?php echo $key;?>)"><?php echo empty($msg[1])?"":$msg[1];?></div>
                    </div>

                </div>
            <?php }
        }
    }
    ?>

</div>
<?php
?>


<script>
    function copyText(id) {
        // 获取对应id的文本内容
        var textToCopy = document.querySelector('.content' + id );

        // 创建一个textarea元素，并将文本内容复制到textarea中
        var textarea = document.createElement('textarea');
        textarea.value = textToCopy.innerText;
// 将textarea元素添加到文档中
        document.body.appendChild(textarea);

        // 选中textarea中的文本
        textarea.select();
        textarea.setSelectionRange(0, 99999); // 兼容移动端

        // 执行复制命令
        document.execCommand('copy');

        // 移除textarea元素
        document.body.removeChild(textarea);

        // 添加复制成功标签
        var successLabel = document.createElement('span');
        successLabel.textContent = ' 复制成功！';
        successLabel.className = 'copy-success';
        textToCopy.appendChild(successLabel);

        // 3秒后移除复制成功标签
        setTimeout(function() {
            textToCopy.removeChild(successLabel);
        }, 3000);
    }
</script>
</body>
</html>

