<?php require_once "header.php"?>
<link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=NanumGothic">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap');
    
    body {
        /**display: flex;
        justify-content: center;**/
        align-items: center;
        min-height: 100vh;
    }

    table {
        border-collapse: collapse;
        background-color: #f0ebdf;
        overflow: hidden;
        width: 1200px;
        border-radius: 10px;
    }

    th, td {
        font-family:'Nanum Gothic',sans-serif;
        text-align: left;
        font-size: 12px;
        padding: 10px;
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
<div class="title">
    <h1>Analysis Result List</h1>
</div>
<div class="table">
<table>
  <tr>
    <th>분석 ID</th>
    <th>설명</th>
    <th>자산</th>
    <th>이익률 시작</th>
    <th>이익률 종료</th>
    <th>백테스트 시작</th>
    <th>백테스트 종료</th>
    <th>인터벌</th>
    <th>ETA</th>
    <th>최대 이터레이션</th>
    <th>로드</th>
    <th>삭제</th> <!-- 13 -->
  </tr>
  <?php foreach($data as $row):?>
  <tr>
    <td><?=$row["ap_name"]?></td>
    <td><?=$row["ap_desc"]?></td>
    <td><?=$row["ap_asset"]?></td>
    <td><?=$row["ap_r_dt_s"]?></td>
    <td><?=$row["ap_r_dt_e"]?></td>
    <td><?=$row["ap_bt_dt_s"]?></td>
    <td><?=$row["ap_bt_dt_e"]?></td>
    <td><?=$row["ap_interval"]?></td>
    <td><?=$row["ap_eta"]?></td>
    <td><?=$row["ap_max_iter"]?></td>
    <td>
    <?php if ($row["ap_is_finish"] == true): ?>
        <button type="button" onclick="location.href='/mvar/result/<?=$row['ap_id']?>'">LOAD</button>
    <?php else:?>
        <button type="button" disabled>WAIT</button>
    <?php endif;?>
    </td>
    <td>
    <?php if ($row["ap_is_finish"] == true): ?>
        <button type="button" class="del-btn" data-id="<?=$row['ap_id']?>">DEL</button>
    <?php else:?>
        <button type="button" disabled>WAIT</button>
    <?php endif;?>
    </td>
  </tr>
  <?php endforeach;?>
</table>
</div>
<div class="btn">
    <button type="button" id="back" onclick="location.href='/'">뒤로</button>
</div>

</body>

<?php require_once "script.php"?>
<script>
    (function() {
        $(".del-btn").on("click", function(e) {
            console.log("del button clicked")
            const $this = $(this)
            const ap_id = $this.attr("data-id")

            console.log("ap_id to del : " + ap_id)

            if (confirm("정말로 삭제하시겠습니까?")) {
                location.href = "/mvar/delete/" + ap_id
            }
        })
    })()
</script>
</html>