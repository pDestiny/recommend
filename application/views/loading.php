<?php require_once "header.php"?>

<body>
    <?=round($time_exp / 60000). "분 " . round(($time_exp % 60000) / 1000). "초 남았습니다. 기다려 주세요."?>
    <div id="myProgress">
        <div id="myBar"></div>
    </div>
</body>

<?php require_once "script.php"?>
<script>
    (function() {
        function start_analysis() {
            $.get("/mvar/ajax_analysis_start/<?=$id?>");
        }

        function move() {
            function call_result() {
                $.getJSON("/mvar/ajax_is_analysis_finished",{
                    "analysis_id": <?=$id?>
                }, function(result) {
                    if(result.is_finish == true) {
                        clearInterval(finish_interval_id)
                        location.href="/mvar/result/<?=$id?>"
                    } else {
                        if(is_first == true) {
                            alert("아직 진행중입니다. 알림창을 끄고 기다려 주세요");
                            is_first = false;
                        }
                    }
                });
            }
            function frame() {
                if (width >= 100) {
                    clearInterval(id);
                    is_first = true
                    finish_interval_id = setInterval(call_result, 1000)
                    
                    
                } else {
                    cur_millis = (new Date()).getTime()
                    console.log(cur_millis, past_millis);
                    width = ((cur_millis - past_millis) / <?=$time_exp?>) * 100
                    console.log(width)
                    elem.css({
                        "width": width + "%"
                    })
                }
            }

            let elem = $("#myBar");
            let id = setInterval(frame, 1000);
            let width = 1
        }

        let d = new Date();
        let past_millis = d.getTime();
        start_analysis()
        move(past_millis);
        
    })()
</script>
</html>