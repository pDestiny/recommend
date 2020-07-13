<?php require_once "header.php"?>
<!-- chart js css-->
<style>
    /* Chart.js */
@keyframes chartjs-render-animation{from{opacity:.99}to{opacity:1}}.chartjs-render-monitor{animation:chartjs-render-animation 1ms}.chartjs-size-monitor,.chartjs-size-monitor-expand,.chartjs-size-monitor-shrink{position:absolute;direction:ltr;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1}.chartjs-size-monitor-expand>div{position:absolute;width:1000000px;height:1000000px;left:0;top:0}.chartjs-size-monitor-shrink>div{position:absolute;width:200%;height:200%;left:0;top:0}
</style>
<style>
    [class$="container"] {
        display: flex;
        justify-content: center;
    }
    [class^="item"] {
        width: 40%;
        display: inline-block;
    }
    .item3-graph {
        width: 80%;
    }
    table {
        border-collapse: collapse;
        background-color: #f0ebdf;
        overflow: hidden;
        width: 100%;
        border-radius: 10px;
    }
    .param-table {
        width:80%;
    }

    th, td {
        font-family:'Nanum Gothic',sans-serif;
        text-align: left;
        font-size: 12px;
        padding: 10px;
        border: 0.5px solid black;
    }

    th {
        background-color: #7691ab;
        color: white;
    }
    .title, .table {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .btn {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        margin-left: -150vh;
    }
    .btn > button {
        width: 100px;
        height: 30px;
    }
</style>
<body>
    <div class="title-container">
        <div class="title">
            <h1>Analysis Result</h1>
        </div>
       
    </div>
    <div class="params-container">
        <table class="param-table">
            <tr>
              <th>분석 ID</th>
              <th>설명</th>
              <th>자산</th>
              <th>이익률 계산 시작</th>
              <th>이익률 계산 종료</th>
              <th>백테스트 시작</th>
              <th>백테스트 종료</th>
              <th>인터벌</th>
              <th>ETA</th>
              <th>최대 이터레이션</th>
            </tr>
            <tr>
              <td><?=$params["ap_name"]?></td>
              <td><?=$params["ap_desc"]?></td>
              <td><?=$params["ap_asset"]?></td>
              <td><?=$params["ap_r_dt_s"]?></td>
              <td><?=$params["ap_r_dt_e"]?></td>
              <td><?=$params["ap_bt_dt_s"]?></td>
              <td><?=$params["ap_bt_dt_e"]?></td>
              <td><?=$params["ap_interval"]?></td>
              <td><?=$params["ap_eta"]?></td>
              <td><?=$params["ap_max_iter"]?></td>
            </tr>
          </table>
    </div>
    <div class="graph-container">
        <div class="item1">
        <canvas id="line-canvas" style="display: block; height: 200px; width: 40%;" class="chartjs-render-monitor"></canvas>
        </div>
        <div class="item2">
        <canvas id="bar-canvas" style="display: block; height: 200px; width: 40%;" class="chartjs-render-monitor"></canvas>
        </div>
    </div>
    <div class="table-container">
        <div class="item-table-1">
            <div class="title">
                <h1>MVAR Portfolio</h1>
            </div>
            <div class="table">
                <table>
                    <tr>
                      <th>EPOCH</th>
                      <th>수익률</th>
                      <th>자산</th>
                    </tr>
                    <?php foreach($result_data["income"]["mvar"] as $row):?>
                        <tr>
                            <td><?=$row["ar_epoch"]?></td>
                            <td><?=$row["income_rate"]?>%</td>
                            <td><?=$row["ar_remain_assets"]?></td>
                        </tr>
                    <?php endforeach;?>
                  </table>
            </div>
        </div>
        <div class="item-table-2">
            <div class="title">
                <h1>일반 Portfolio</h1>
            </div>
            <div class="table">
                <table>
                    <tr>
                      <th>EPOCH</th>
                      <th>수익률</th>
                      <th>자산</th>
                    </tr>
                    <?php foreach($result_data["income"]["ant"] as $row):?>
                        <tr>
                            <td><?=$row["ar_epoch"]?></td>
                            <td><?=$row["income_rate"]?>%</td>
                            <td><?=$row["ar_remain_assets"]?></td>
                        </tr>
                    <?php endforeach;?>
                  </table>
            </div>
            
        </div>
    </div>
    <div class="graph2-container">
        <div class="item3-graph">
        <canvas id="line2-canvas" style="display: block; height: 350px; width: 80%;" class="chartjs-render-monitor"></canvas>
        </div>
    </div>
    <div class="table2-container">
        <div class="item-table2-1">
            <div class="title">
                <h1>MVAR Portfolio</h1>
            </div>
            <div class="table">
                <table>
                    <tr>
                      <th>EPOCH</th>
                      <th>회사명</th>
                      <th>비중</th>
                    </tr>
                    <?php $counter = 0?>
                    <?php foreach($result_data["weight"]["mvar"] as $val):?>
                    <tr>
                        <?php if($counter == 0){
                            $counter += 1;?>
                        <td rowspan="<?=$n?>"><?=$val["ar_epoch"]?></td>
                        <?php } else if($counter == $n - 1)
                        { 
                        $counter = 0;
                        } else { 
                            $counter += 1;
                        }?>
                        <td><?=$val["ar_name"]?></td>
                        <td><?=$val["ar_weight"]?>%</td>
                    </tr>
                    <?php endforeach;?>
                  </table>
            </div>
        </div>
        <div class="item-table2-2">
             <div class="title">
                <h1>일반 Portfolio</h1>
            </div>
            <div class="table">
                <table>
                    <tr>
                      <th>EPOCH</th>
                      <th>수익률</th>
                      <th>자산</th>
                    </tr>
                <?php $counter = 0?>
                    <?php foreach($result_data["weight"]["ant"] as $val):?>
                    <tr>
                        <?php if($counter == 0){
                            $counter += 1;?>
                        <td rowspan="<?=$n?>"><?=$val["ar_epoch"]?></td>
                        <?php } else if($counter == $n - 1)
                        { 
                        $counter = 0;
                        } else { 
                            $counter += 1;
                        }?>
                        <td><?=$val["ar_name"]?></td>
                        <td><?=$val["ar_weight"]?>%</td>
                    </tr>
                    <?php endforeach;?>
                  </table>
            </div>
        </div>
    </div>

    <div class="btn">

    </div>

</body>
<?php require_once "script.php"?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.0.0-alpha/Chart.min.js"></script>
<script>
    (function(global) {
        global.graph_data = {
            income: {
                mvar: <?=json_encode($result_data["income"]["mvar"])?>,
                ant: <?=json_encode($result_data["income"]["ant"])?>,
                std: <?=json_encode($result_data["income"]["std"])?>
            },
            weight: {
                mvar: <?=json_encode($result_data["weight"]["mvar"])?>
            }
        }
        global.graph_data.weight.grouped_mvar = _.groupBy(graph_data.weight.mvar, d => d.ar_name)
        console.log(global.graph_data.weight.grouped_mvar);
    })(this)
</script>


</html>
