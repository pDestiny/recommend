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
		#stock-box {
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
					<input type="text" id="r_datepicker_s" name="r_datepicker_s" placeholder="시작날짜" required autocomplete="off" value="2010-01-01">
					~
					<input type="text" id="r_datepicker_e" name="r_datepicker_e" placeholder="종료날짜" required autocomplete="off">
				</div>
				<div class="box">
					<label>백테스팅 기간</label>
					<input type="text" id="bt_datepicker_s" name="bt_datepicker_s" placeholder="시작날짜" required autocomplete="off">
					~
					<input type="text" id="bt_datepicker_e" name="bt_datepicker_e" placeholder="종료날짜" required autocomplete="off" value="2013-01-01">
				</div>
				<div class="box">
					<label>주식 선택</label><input type="text" id="sch-target"><button type="button" class="search-stock">주식 검색</button>
					<div id="stock-box">

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
	<div style="display:None;" class="template">
	<div class="stock-select">
		<input type="checkbox" name="stock_id[]" id="">
		<label for="">
		</label>
	</div>
</template>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.19/lodash.min.js"></script>
<script>
	$(function () {
		$("[name*='datepicker']").datepicker({
			changeYear:true,
			dateFormat: "yy-mm-dd"
		}).on("change", e => {
			const $this = $(e.target)
			const name_comps = $this.attr("name").split("_")
			if(_.last(name_comps) == "s") {
				
				// 수익률인지 아니면 벡테스트 기간인지
				if(_.first(name_comps) == "r") {
					
				}

			} else {
				// 수익률인지 아니면 벡테스트 기간인지
				if(_.first(name_comps) == "r") {

				}
			}
		})

		$(".search-stock").on('click', () => {
			let r_date_s = $("[name='r_datepicker_s']").val()
			let bt_date_e = $("[name='bt_datepicker_e']").val()
			let stock_sch_content = $("#sch-target").val()
			$.getJSON("mvar/ajax_get_stocks", {
				r_date_s, bt_date_e, stock_sch_content 
			}, data => {
				let fragment = $(new DocumentFragment())
				let $template = $(".template > div.stock-select").clone()

				if (_.size(data) == 0) {

					alert(`${stock_sch_content}는 존재하지 않습니다. 확인해 주시기 바랍니다.`)

				} else {

					let name = data[0].name
					let code = data[0].code

					if ($("#sp" + code).length != 0)  {
						alert(`${stock_sch_content} 은 이미 검색하셨습니다.`)
					} else {
						let checkbox_structure =  $template.clone()

						checkbox_structure.children("label").attr(
							{
								"for": "sp" + code
							}
						).html(name)

						checkbox_structure.children("input:checkbox").attr({
							"id": "sp" + code
						}).val(code)

						fragment.append(checkbox_structure)

						$("#stock-box").append(fragment)
					}

				
				}


				
			})
		})
	});

</script>

</html>
