<?php require_once "header.php"?>

<body>

	<div id="container">
		<h1>MVAR(Minimum VARiance) Portfolio Simulator</h1>
		<div class="con">
			<form action="mvar/loading" method="post" id="param_form">
				<div class="box">
					<label>분석 ID</label><input type="text" name="anlysis_name" id="anlysis_name" name="asset" required value="test-ap-name-1" maxlength="255"><button type="button" id="dup-check">중복검사</button>
				</div>
				<div class="box">
					<label>설명</label><input type="text" id="desc" name="desc" value="description" maxlength="63">
				</div>
				<div class="box">
					<label>기초자산</label><input type="text" id="asset" name="asset" required value="1000000">
				</div>
				<div class="box">
					<label>이익률 계산 기간</label>
					<input type="text" id="r_dt_s" name="r_dt_s" placeholder="시작날짜" required autocomplete="off" value="2017-12-01">
					~
					<input type="text" id="r_dt_e" name="r_dt_e" placeholder="종료날짜" required autocomplete="off" value="2018-01-01">
				</div>
				<div class="box">
					<label>백테스팅 기간</label>
					<input type="text" id="bt_dt_s" name="bt_dt_s" placeholder="시작날짜" required autocomplete="off" value="2018-01-01">
					~
					<input type="text" id="bt_dt_e" name="bt_dt_e" placeholder="종료날짜" required autocomplete="off" value="2018-03-01">
				</div>
				<div class="box">
					<label>주식 선택</label><input type="text" id="sch-target"><button type="button" class="search-stock">주식 검색</button>
					<div id="stock-box">
						<div class="stock-select">
							<input type="checkbox" name="stock_id[]" id="sp660" value="660">
							<label for="sp660">SK하이닉스</label>
						</div><div class="stock-select">
							<input type="checkbox" name="stock_id[]" id="sp1040" value="1040">
							<label for="sp1040">CJ</label>
						</div><div class="stock-select">
							<input type="checkbox" name="stock_id[]" id="sp69730" value="69730">
							<label for="sp69730">DSR제강</label>
						</div></div>
					</div>
				</div>
				<div class="box">
					<label>비중 리밸런싱 인터벌</label> <input type="text" name="interval" value="30"> days
				</div>

				<div class="box">
					<label>러닝 레이트(ETA)</label><input type="text" name="eta" value=.5>
				</div>

				<div class="box">
					<label>최대 이터레이션 횟수</label><input type="text" name="max_iter" value="2000">
				</div>
				<div class="btns">
					<button type="button" class="start_sim">시뮬레이션 수행</button>
					<button type="button" class="load_sim" onclick="location.href='mvar/list'">시뮬레이션 로드</button>
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

<?php require_once "script.php"?>
<script>
	$(function () {
		function check_dup_ap_name(ap_name) {
			$.getJSON("/mvar/ajax_dup_check", {
				"ap_name": ap_name
			}, function(result) {
				if (result.is_unique != true) {
					alert("중복된 분석 아이디 입니다. 다시 작성해 주세요")
				} else {
					alert("중복되지 않은 아이디입니다. 사용하셔도 좋습니다.")
				}
			})
		}

		//validation
		$(".start_sim").on("click", function(e) {

			
			const $this = $(e.target)
			const anlysis_name = $("[name=anlysis_name]").val()
			let is_pass = true;
			//분석 아이디가 중복되지 않았는지 확인
			$.ajax({
				url: "/mvar/ajax_dup_check",
				dataType: 'json',
				async: false,
				data: {
					"ap_name": anlysis_name
				},
				success: function(result) {
					if (result.is_unique != true) {
						alert("중복된 분석 아이디 입니다. 다시 작성해 주세요")
						is_pass = false
					} 
				}
			})

			//기초 자산이 0초과 인지 확인 할 것
			let vali = $("[name=asset]").val()

			if (vali == '' || parseInt(vali) < 1) {
				alert("자산은 반드시 0보다 큰 값을 가져야 합니다.")
				is_pass = false
			}
			

			//주식을 하나라도 선택했는지 확인할것
			vali = $("#stock-box > .stock-select > input:checked").length
			
			if (vali <= 1) {
				alert("반드시 주식을 두개이상 선택해야 합니다.")
				is_pass = false
			}
			

			//인터벌 0 초과인지 확인 할 것
			vali = $("[name=interval]").val()

			if(vali == '' || parseInt(vali) < 1) {
				alert("인터벌은 반드시 1 이상이여야 합니다.")
				is_pass = false
			}

			//러닝 레이트가 숫자를 작성 했는지 확인 하기
			vali = $("[name=eta]").val()
			
			if (vali == '' || parseFloat(vali) <= 0) {
				alert("eta은 반드시 0보다 큰 값을 가져야 합니다.")
				is_pass = false
			}
			
			//최대 이터레이션 횟수가 2000 번 이상인지 확인하고 미만일 경우 최소값에 수렴하지 못할 수 있음을 경고
			vali = $("[name=max_iter]").val()

			if (vali == '' || parseInt(vali) < 0) {
				alert("최대 이터레이션 횟수는 반드시 0보다 큰 값을 가져야 합니다.")
				is_pass = false
			} else if(parseInt(vali) < 2000) {
				if(!confirm("최대 이터레이션 횟수가 2000번 미만이어 분산값이 최소 값에 수렴하지 못할 수 있습니다. 계속하시겠습니까?")) {
					is_pass = false
				}
			}

			//모든 조건을 통과 했을 경우
			if(is_pass == true) {
				$("#param_form").submit()
			}
		})

		$("[name*='dt']").datepicker({
			changeYear:true,
			dateFormat: "yy-mm-dd",
			yearRange: "2000:2020"
		})

		$(".search-stock").on('click', () => {
			let r_date_s = $("[name='r_dt_s']").val()
			let bt_date_e = $("[name='bt_dt_e']").val()
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

		// 수익률 계산 종료 날짜를 선택할 경우 벡테스트 기간을 초기화 한다.
		$("[name=r_dt_e]").on("change", function(e) {
			$("[name^='bt_dt']").val("")
		})

		// 종료 날짜는 시작 날짜보다 앞서서는 안된다
		$("[name$='dt_e']").on("change", function(e) {
			const $this = $(e.target)
			
			const prefix = $this.attr("name").split("_")[0]

			const start_dt = $("[name="+ prefix + "_dt_s]").val()

			if (start_dt != false){
				if ($this.val() <= start_dt) {
					alert("종료 날짜는 시작 날짜보다 앞서서는 안됩니다.")
					$this.val("")
				}
			}
		})

		// 백테스트 시작 날짜는 수익률 계산 종료날짜보다 앞서야 한다
		$("[name=bt_dt_s]").on("change", function(e){
			console.log("hello world")
			const $this = $(e.target)
			const r_date_e = $("[name=r_dt_e]").val()
			console.log(r_date_e)

			if ($this.val() < r_date_e) {
				alert("백테스트 시작 날짜는 수익률 계산 종료날짜보다 앞서야 합니다.")
				$this.val("")
			}
		})

		$("#dup-check").on("click", function() {
			const anlysis_name = $("[name=anlysis_name]").val()

			if (anlysis_name.length == 0) {
				alert("분석 ID를 반드시 적으셔야 합니다")
			}
			else if(anlysis_name.indexOf(" ") != -1) {
				alert("분석 이름에 빈칸은 허용되지 않습니다.")
			}
			else {
				check_dup_ap_name(anlysis_name)
			}
		})
		
		
	});

</script>

</html>
