<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>MVAR Portfolio Simulation</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
	<style>
		#stock_box {
			border: 1px solid black;
			height: 300px;
			width: 500px;
			overflow: scroll;
		}

	</style>
</head>

<body>

	<div id="container">
		<h1>MVAR(Minimum VARiance) Portfolio Simulator</h1>
		<div class="con">
			<form action="mvar/result" method="post">
				<div class="box">
					<label>기초자산</label><input type="text" id="asset" name="asset" required>
				</div>
				<div class="box">
					<label>이익률 계산 기간</label>
					<input type="text" id="r_datepicker_s" name="r_datepicker_s" placeholder="시작날짜" required>
					~
					<input type="text" id="r_datepicker_e" name="r_datepicker_e" placeholder="종료날짜" required>
				</div>
				<div class="box">
					<label>백테스팅 기간</label>
					<input type="text" id="bt_datepicker_s" name="bt_datepicker_s" placeholder="시작날짜" required>
					~
					<input type="text" id="bt_datepicker_e" name="bt_datepicker_e" placeholder="종료날짜" required>
				</div>
				<div class="box">
					<label>주식 선택</label><button type="button">주식 검색</button>
					<div id="stock_box">

					</div>
				</div>
				<div class="box">
					<label>비중 리밸런싱 인터벌</label> <input type="text" name="interval"> days
				</div>

				<div class="box">
					<label>톨러런스</label><input type="text" name="tolerance">
				</div>

				<div class="box">
					<label>러닝 레이트(ETA)</label><input type="text" name="eta">
				</div>

				<div class="box">
					<label>최대 이터레이션 횟수</label><input type="text" name="max_iter">
				</div>
				<div class="btns">
					<button type="submit" class="start_sim">시뮬레이션 수행</button>
					<button class="init" type="button">초기화</button>
				</div>
			</form>
		</div>
	</div>

</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
	$(function () {
		$("[name*='datepicker']").datepicker({
			changeYear:true,
			dateFormat: "yy-mm-dd"
		})
	});

</script>

</html>
