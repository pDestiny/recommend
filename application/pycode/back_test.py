import numpy as np
import sympy as sym
import pandas as pd
import joblib
import seaborn as sns
import matplotlib.pyplot as plt
from datetime import timedelta
from time import time
import pymysql as mydb


def hr(saying):
    print(f"\n======{saying}======\n")

def mvar_gd(data, r_dt_s, r_dt_e, eta_hyper, max_iter):
    
    data = data.loc[(data["Date"] > r_dt_s) & (data["Date"] < r_dt_e)]
    
    # 수익률 백터
    grouped = data.groupby(["Name", "Code"])
    
    income_rate_pct_change = pd.DataFrame(columns=["Name", "Code", "pct_change"])
    
    income_rate_v = []
    
    for (name, code), g in grouped:
        gcopy = g.copy()
        gcopy["pct_change"] = gcopy.loc[:,"Close"].pct_change()
        income_rate_v.append(gcopy.loc[:, "pct_change"].dropna().to_list())
        income_rate_pct_change = income_rate_pct_change.append(gcopy.loc[:,["Name", "Code", "pct_change"]], ignore_index=True)
        
    income_rate_pct_change = income_rate_pct_change.sort_values("Code", ascending=True)
    
    # 수익률 분산
    income_var = income_rate_pct_change.groupby(["Name", "Code"]).agg("var").sort_values("Code", ascending=True)
    
    income_var = income_var.rename(columns={
        "pct_change": "r_var"
    }).reset_index()
    
    
    # 수익률, 분산 벡터화
    
    income_var_v = np.array(income_var.loc[:, "r_var"])
    
    n = len(income_var_v)
    
    # stochastic weight selection
    weights = np.array([1 / n for _ in range(n)])
    
    # 분산 공식 작성. w1 ~ wk 까지
    W = sym.symbols(" ".join(["w" + str(num) for num in range(1, n + 1)]))
    
    f = np.sum([W[idx]**2 * income_var_v[idx] for idx in range(n)])
    
    
    for i in range(0, n - 1):
        for j in range(i + 1, n):
            if len(income_rate_v[i]) > len(income_rate_v[j]):
                income_rate_v[i] = income_rate_v[i][:len(income_rate_v[j])]
            elif len(income_rate_v[i]) < len(income_rate_v[j]):
                income_rate_v[j] = income_rate_v[j][:len(income_rate_v[i])]
                
            target = np.stack([income_rate_v[i], income_rate_v[j]], axis=0)
            f += 2 * W[i] * W[j] * np.cov(target)[0, 1]
            
    # 편미분 수행
    diffs = []
    
    for i in range(0, n):
        diffs.append(f.diff(W[i]))
    
    for e in range(max_iter):
        
        for diff, i in zip(diffs, range(0, n)):
            weights[i] = weights[i] - eta_hyper * sym.lambdify([W], diff, "numpy")(weights)

        portfolio_var = sym.lambdify([W], f, "numpy")(weights)
    
    weight = np.where(weights < 0, 0, weights)
    
    weight_result = pd.DataFrame(columns=["name","code", "weight"])    
    
    weight_result["name"] = income_var["Name"]
    weight_result["code"] = income_var["Code"]
    
    weight_result["weight"] =  weight / np.sum(weight)
    # result data decorating
    
    return weight_result

def back_test(data, asset, r_dt_s, r_dt_e, bt_dt_s, bt_dt_e, interval=30, eta_hyper = .5, max_iter=2000):
    #print(data)
    """
    data : 백테스트 용 데이터
    r_dt_s, r_dt_e : 최소 수익률을 구할 구간. 이 구간의 길이만큼 앞으로도 분산 레이트를 구하게 된다.
    bt_dt_s, bt_dt_e : 백테스트 기간.
    interval : 백테스트 구간 이내에 분산 rateing 제조정 인터벌. 기본 한달.(30일)
    eta_hyper : 러닝 레이트. 기본값 .5
    max_iter : 경사하강 최대 이터레이션 횟수 기본값은 2000
    """
    # 최소 분산을 구한다는 것은 위험한 상황에서도 지속적인 수익률을 창출해 낼 수 있을 것인가에 대한 문제이다.
    # 확인해야 할 것은 백테스트 interval이 진행 됨에 따라 실제 수익률의 변동성을 체크해 보는 것이다.
    # 위에서 만든 분산을 통한 레이팅은 어떤 영향을 끼칠 지 알 수가 없다.
    # 그렇다면 확인해야 할 것은 레이팅이 최적의 비율을 계산한건지, weight가 분산값에 영향을 많이 끼치는건지에 대해 
    # 알아봐야 한다.
    
    # back test 안에서 사용할 데이터 정제
    
    
    # 날짜 str에서 datetime으로 변환
    r_dt_s = pd.to_datetime(r_dt_s)
    r_dt_e = pd.to_datetime(r_dt_e)
    bt_dt_s = pd.to_datetime(bt_dt_s)
    bt_dt_e = pd.to_datetime(bt_dt_e)
    
    # 수집할 데이터 프레임 정의
    colmuns = ["kind","epoch", "name", "code", "income_rate","start_assets","remain_assets", "weight"]
    dtypes = [np.str, np.int16, np.str, np.str, np.float64, np.float64, np.float64]
    comparedata = pd.DataFrame(columns=colmuns)
    for col, dtype in zip(colmuns, dtypes):
        comparedata[col] = comparedata[col].astype(dtype)
    
    comparedata.info()
    
    # backtesting 시작
    epoch = 1
    mvar_asset = asset
    ant_asset = asset
    n = len(data["Name"].unique())
    while r_dt_e <= (bt_dt_e - timedelta(days=interval)):
        # back test 안에서 사용할 데이터 정제
        print("=====",r_dt_s, r_dt_e, "=====\n")
        bt_data = data.loc[(data["Date"] >= bt_dt_s) & (data["Date"] < (bt_dt_s + timedelta(days=interval)))]
        
        #print(bt_data.head())
        grouped_bt_data = bt_data.groupby(["Name", "Code"])
        
        first_price = grouped_bt_data.nth(0)
        last_price = grouped_bt_data.nth(-1)
        
        hr("first_price")
        print(first_price)
        hr("last_price")
        print(last_price)
        income_rate = np.array((last_price["Close"] - first_price["Close"]) / first_price["Close"])
        
        hr("income rate")
        print(income_rate)

        hr(f"epoch {epoch} started")
        s_time = time()
        mvar_data = mvar_gd(data, r_dt_s, r_dt_e, eta_hyper=eta_hyper, max_iter=max_iter)
        e_time = time()
        hr(f"epoch {epoch} completed : time exp : {e_time - s_time}")
        # colmuns = ["kind","epoch", "name", "code", "income_rate","start_assets","remain_assets", "weight"]
        
        # mvar data 
        mvar_data["kind"] = "MVAR"    
        mvar_data["epoch"] = epoch
        mvar_asset = np.sum(mvar_asset) * mvar_data["weight"]
        mvar_data["start_assets"] = mvar_asset
        mvar_data["remain_assets"] = mvar_data["start_assets"] +  mvar_data["start_assets"] * income_rate
        mvar_asset = mvar_data["remain_assets"]
        mvar_data["income_rate"] = income_rate
        
        # ant data 
        ant_data = mvar_data.copy()
        ant_data["kind"] = "ant"
        ant_asset = np.sum(ant_asset) * np.array([1 / n for _ in range(n)])
        ant_data["weight"] = np.array([1 / n for _ in range(n)])
        ant_data["start_assets"] = ant_asset
        ant_data["remain_assets"] = ant_data["start_assets"] +  ant_data["start_assets"] * income_rate
        ant_asset = ant_data["remain_assets"]
        ant_data["income_rate"] = income_rate   
        
        # mvar ant 데이터 합침
        mvar_ant_data = pd.concat([mvar_data, ant_data], axis=0)
        
        comparedata = pd.concat([comparedata, mvar_ant_data], axis=0)
        hr("total_data")
        print(comparedata)
        
        r_dt_s += timedelta(days=interval)
        r_dt_e += timedelta(days=interval)
        bt_dt_s += timedelta(days=interval)
        epoch += 1
        
        
    return comparedata


# id 값으로 params 데이터를 로드 




if __name__ == "__main__":
    ap_id = 47

    conn = mydb.connect(host="pdestiny.xyz", user="user1", password="0410s", db="stock_analysis")

    cur = conn.cursor()

    params = None

    cur.execute("""
        SELECT * FROM analysis_params where ap_id = %s
    """, (ap_id))

    result = cur.fetchone()

    print(result)

    _, name, _, asset, r_dt_s, r_dt_e, bt_dt_s, bt_dt_e, interval, eta, max_iter, *_ = result

    stock_codes = []

    cur.execute("""
        select * from analysis_stocks where as_ap_id = %s
    """, (ap_id))
    
    for code in cur.fetchall():
        stock_codes.append(code[1])

    print(stock_codes)

    in_data = " or ".join(["code = %s" for _ in range(len(stock_codes))])

    print(in_data)

    sql = f"""
        select name, code, date, close from stock_data where ({in_data}) 
    """

    print(sql)

    stock_df = pd.DataFrame(columns = ["Name", "Code", "Date", "Close"])

    cur.execute(sql, stock_codes)
    limit = 4
    for stock_data in cur.fetchall():
        row = {
            "Name": stock_data[0],
            "Code": stock_data[1],
            "Date": stock_data[2],
            "Close": stock_data[3]
        }
        stock_df = stock_df.append(row, ignore_index=True)

    stock_df["Date"] = stock_df["Date"].astype("datetime64[ns]")

    bt_result = back_test(stock_df, asset, r_dt_s, r_dt_e, bt_dt_s, bt_dt_e, interval, eta, max_iter)

    for data in bt_result.iterrows():
        idata = [ap_id] + data[1].to_list()
        values = ",".join(["%s" for _ in idata])
        sql = f"""
            insert into analysis_result values ({values})
        """
        cur.execute(sql, idata)

    conn.commit()

        