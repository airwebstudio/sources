with dt as (
        	select (trunc(SYSDATE, 'IW') - (5-%L%)*7) as start_date from dual
    	),

    	srv AS (
            	SELECT user_id, Sum(CASE WHEN srv_id = 9 THEN 1 ELSE 0 END ) zphone, Sum(CASE WHEN srv_id = 25 THEN 1 ELSE 0 END ) ztv
            	FROM utm_rep.vw_users_psm
            	WHERE srv_id IN (9, 25) AND is_active = 1 AND fid = utm_rep.report_helper.f_get_fid_by_date((SELECT (start_date + 6) FROM dt))
            	GROUP BY user_id

    	),

    	tdh as (
        	(SELECT DISTINCT tdh_uid FROM utm_rep.traffic_discount_history WHERE tdh_mb > 0 AND tdh_date between (select start_date from dt) and (select start_date + 6 from dt))
    	)

    	SELECT u.id, u.full_name, u.address_district, u.address_name, u.user_flat, u.current_bank_account as phone1, u.tax_number as phone2, u.phone_extra as phone3, trunc(u.dt_reg_date) reg_date
             	, CASE WHEN Nvl(srv.zphone,0)>0 THEN 1 ELSE 0 END zphone, CASE WHEN Nvl(srv.ztv,0)>0 THEN 1 ELSE 0 END ztv

    	FROM utm_rep.vw_users u
    	JOIN tdh ON (tdh.tdh_uid = u.id)
    	left JOIN srv ON srv.user_id = u.id
    	WHERE
      	u.unit_id = %unitid%
      	AND u.is_juridical = 0
      	AND u.is_service = 0




WITH usrs AS (SELECT Value AS user_id FROM tmp_integer_array),
        	psms AS (
              	SELECT * FROM
            	(
              	SELECT user_id, str_code, psm_name, cost
              	FROM utm_rep.vw_users_psm p
              	WHERE fid = %fid%
                  	AND user_id IN (SELECT user_id FROM usrs)
                  	AND str_code IN ('rent', 'internet', 'ztv', 'voip')
                  	AND is_active = 1
            	) pivot (
              	LISTAGG(psm_name, ', ') WITHIN GROUP (ORDER BY psm_name) AS serv,
              	Sum(cost) AS cost
              	FOR str_code IN ('rent' AS rent, 'internet' AS inet, 'ztv' AS ztv, 'voip' AS voip))
            	)

         	SELECT usrs.user_id,
            	psms.*
            	, Decode((SELECT user_id FROM utm_rep.active_users WHERE user_id = usrs.user_id AND fid = %fid%), NULL, 0, 1) is_active
            	, (SELECT Sum(pdh.num) d_sum FROM utm_rep.prod_discount_history pdh WHERE pdh.fid = %fid% AND pdh.user_id = usrs.user_id) d_sum
            	, u.unit_id
            	, Nvl((SELECT bhm.block_status from utm_rep.block_history_month bhm WHERE bhm.block_uid = usrs.user_id AND bhm.fid = %fid%), 0) user_block_fid
            	, u.user_block
            	, Decode((SELECT user_id FROM utm_rep.user_groups_belongs WHERE grp_id = 4 AND user_id = usrs.user_id), NULL, u.current_bank_account, 1, '') sms_phone

         	FROM
            	usrs
         	JOIN psms ON (psms.user_id = usrs.user_id)
         	JOIN utm_rep.users u ON (u.id = usrs.user_id)
WITH
   	psm AS (
     	SELECT user_id, psm_name, srvmode_id, ps.prod_name, ps.str_code, srv_id, created_at FROM utm_rep.user_payables up
     	JOIN utm_rep.prod_serv_modes psm ON psm.id = up.srvmode_id
     	JOIN utm_rep.products_services ps ON (ps.id = psm.srv_id)
  	 
     	where
     	fid = %fid%
  	 
   	),
   	psm_srv AS (
     	SELECT user_id ,str_code, listagg(Trim(psm_name), ', ') within GROUP (ORDER BY 1) psm_list
     	FROM utm_rep.vw_users_psm
     	where
     	fid = %fid%
     	AND str_code IN ('internet', 'ztv', 'voip')  AND is_active = 1 GROUP BY user_id, str_code
   	)
  	 
  	 
   	SELECT u.id, bs.prod_list prod_list1, psm3.psm_list psm_list1, psm1.psm_list psm_list2, psm2.psm_list psm_list3,
   	psm7.psm_list psm_list4, psm7.psm_list_dates, psm5.psm_list psm_list5, pdh.pdh_list, u.dt_reg_date, Nvl2(cau.user_id, 1, 0) is_active
    	FROM (
   	SELECT user_id, listagg(prod_name, ', ') within GROUP (ORDER BY 1) prod_list FROM (
   	SELECT user_id, Min(prod_name) prod_name FROM psm
   	WHERE
   	NOT EXISTS
   	(
     	SELECT 1 FROM utm_rep.user_payables up1
     	JOIN utm_rep.prod_serv_modes psm1 ON psm1.id = up1.srvmode_id
  	 
     	WHERE
     	fid = (%fid% -1)
     	AND user_id = psm.user_id
     	AND
     	(
       	(up1.srvmode_id &lt;&gt; 20
       	AND
       	psm1.srv_id = psm.srv_id)
  	 
       	OR
       	(up1.srvmode_id = 20
       	AND psm1.srv_id = psm.srv_id
       	AND up1.srvmode_id = psm.srvmode_id)
  	 
     	)
   	)
  	 
   	GROUP BY user_id, srv_id)
  	 
   	GROUP BY user_id
   	)
   	bs
  	 
   	JOIN utm_rep.users u ON (bs.user_id = u.id)
  	 
   	left JOIN (SELECT user_id, psm_list FROM psm_srv WHERE str_code='ztv') psm1 ON (psm1.user_id = bs.user_id)
   	left JOIN (SELECT user_id, psm_list FROM psm_srv WHERE str_code='voip') psm2 ON (psm2.user_id = bs.user_id)
   	left JOIN (SELECT user_id, psm_list FROM psm_srv WHERE str_code='internet') psm3 ON (psm3.user_id = bs.user_id)
   	left JOIN (SELECT user_id, listagg(psm_name, ', ') within GROUP(ORDER BY 1) psm_list FROM psm WHERE str_code='rent' GROUP BY user_id) psm5 ON (psm5.user_id = bs.user_id)
   	left JOIN (
  	 
   	SELECT
   	user_id, listagg(qnt_cmt , ', ') within GROUP(ORDER BY 1) pdh_list FROM
  	 
     	(SELECT user_id, Nvl(Sum(qnt), 0) || Nvl2(Min(prod_comments), '(' || Trim(Min(prod_comments) || ')'), '') qnt_cmt
     	FROM utm_rep.prod_discount_history
     	WHERE
     	fid = %fid%

     	GROUP BY user_id, prod_code)

     	GROUP BY user_id

     	) pdh ON (pdh.user_id = bs.user_id)
  	 
  	 
   	left JOIN
     	(SELECT
       	user_id,
       	listagg(psm_name, ', ') within GROUP (ORDER BY 1) psm_list,
       	listagg(created_at, ', ') within GROUP (ORDER BY 1) psm_list_dates
  	 
  	 
       	FROM psm WHERE str_code IN ('sell', 'rent')
  	 
       	AND NOT EXISTS (
  	 
       	SELECT 1 FROM utm_rep.user_payables up1
  	 
         	WHERE
         	fid = (%fid% -1)
         	AND up1.srvmode_id = psm.srvmode_id
         	AND up1.user_id = psm.user_id
  	 
       	)
  	 
       	GROUP BY user_id
  	 
   	) psm7 ON (psm7.user_id = bs.user_id)
  	 
   	left JOIN utm_rep.current_active_users cau ON (cau.user_id = bs.user_id)

   	where u.unit_id IN (%UNITS%)



SELECT * FROM
     	(
       	SELECT full_name, i_type FROM (
         	SELECT 'bill_spb' AS i_type, full_name FROM utm_rep.vw_users u
         	JOIN "users"@UTM_BILL uu ON (uu."id" = u.id)
         	WHERE Lower(full_name) LIKE Lower('%%txt%%') AND u.address_district = '200' AND ROWNUM&lt;=500

         	UNION ALL

         	SELECT 'bill_reg' AS i_type, full_name FROM utm_rep.vw_users_r u
         	JOIN "users"@REGIONS_UTM_ZBILL uu ON (uu."id" = u.id)
         	WHERE Lower(full_name) LIKE Lower('%%txt%%')  AND u.address_district = '200' AND ROWNUM&lt;=500

         	UNION ALL

         	SELECT 'b1_spb' AS i_type, user_name AS full_name FROM utm_rep.users_b1 u
         	JOIN "users"@BUILD_1 uu ON (uu."user_id" = u.user_id)
         	WHERE Lower(user_name) LIKE Lower('%%txt%%') AND ROWNUM&lt;=500

         	UNION ALL

         	SELECT 'b2_spb' AS i_type, user_name AS full_name FROM utm_rep.users_b2 u
         	JOIN "users"@BUILD_2 uu ON (uu."user_id" = u.user_id)
         	WHERE Lower(user_name) LIKE Lower('%%txt%%') AND ROWNUM&lt;=500

         	UNION ALL

         	SELECT 'b_reg' AS i_type, user_name AS full_name FROM utm_rep.users_br u
         	JOIN "users"@BUILD_R uu ON (uu."user_id" = u.user_id)
         	WHERE Lower(user_name) LIKE Lower('%%txt%%') AND ROWNUM&lt;=500
       	)


     	)
     	pivot (Count(*) FOR i_type IN('bill_spb' AS bill_spb, 'bill_reg' AS bill_reg, 'b1_spb' AS b1_spb, 'b2_spb' AS b2_spb, 'b_reg' AS b_reg))

 WITH
    	srvs AS
    	(
       	SELECT user_id
       	FROM utm_rep.user_serv_modes usm
       	JOIN utm_rep.users u ON (u.id = usm.user_id)
       	WHERE
       	fid =  utm_rep.report_helper.f_current_fid
       	AND is_active = 1
       	AND u.unit_id IN (%UNITS%)

       	GROUP BY user_id
       	HAVING Count(user_id) &gt; 1

    	),

    	builders AS (
      	SELECT ub1.user_login, a.application_clientid AS user_id from utm_rep.applications_b a
      	JOIN utm_rep.users_b1 ub1 ON (ub1.user_id = a.application_builder)
      	WHERE a.application_clientid IN (SELECT user_id FROM srvs)
    	),

    	fst_dts AS (
        	SELECT user_id, Min(discount_date) dt FROM utm_rep.prod_discount_history
        	WHERE user_id IN (SELECT user_id FROM srvs)
        	GROUP BY user_id

    	),

    	st_dts AS (
        	select Max(time_stamp) dt, user_id
        	from utm_rep.all_history ah
        	join utm_rep.all_history_details ahd using (history_id)
        	WHERE old_value &lt;&gt; new_value
        	and user_id IN (SELECT user_id FROM srvs)
        	group by user_id
    	)


    	SELECT u.id, u.tariff, u.servpack_id, std.dt st_date, fd.dt f_date, b.user_login

    	FROM utm_rep.vw_users u
    	JOIN srvs ON (srvs.user_id = u.id)

    	left JOIN builders b ON (b.user_id = u.id)
    	left JOIN fst_dts fd ON (fd.user_id = u.id)
    	left JOIN st_dts std ON (std.user_id = u.id)





SELECT
     	u.id, u.login, u.full_name, u.user_district, u.address_name, u.unit_name, u.user_block, u.current_inet_state, u.bill, u.bonus, u.tariff, tcg.tariffs_groups
     	, Nvl(u.pdt_summ_qnt, '0') pdt_summ_qnt
     	, (SELECT Sum(qnt) FROM utm_rep.prod_discount_history WHERE user_id = u.id) pdh_summ_qnt
     	, comments
     	, current_disc_qnt

     	FROM
     	utm_rep.vw_users u
     	left JOIN utm_rep.vw_tariffs_current_groups tcg ON (tcg.tid = u.tariff)

     	WHERE
     	u.user_district= '17'
     	AND dt_reg_date BETWEEN To_DATE('2010-10-01', 'YYYY-MM-DD') AND To_DATE('2012-10-01', 'YYYY-MM-DD')
     	ORDER BY u.id






WITH all_numbers AS (

    	select cn.city_number, um.fid, um.user_id
     	from
       	utm_rep.voip_znumber_options vo
       	join utm_rep.voip_city_numbers cn on ( cn.city_number = vo.znumber ) -- для городских номеров z-номер равен номеру городского  
       	left join utm_rep.user_serv_modes um on ( um.usmid = vo.usmid ) -- берем финпериод и договор
     	where
       	( cn.city_number between 3860200 and 3860999 or cn.city_number between 6467000 and 6468999 or cn.city_number between 6768000 and 6768999 )
  	 
    	)
  	 
  	 
   	SELECT Min(t.city_number) as phone_number, sum(up.payable) as sum_all from
     	(
       	(SELECT * FROM all_numbers WHERE user_id IS NOT NULL	 
         	group by  city_number, fid, user_id)
      	 
     	) t
     	left join utm_rep.user_payables up on ( up.fid = t.fid and up.user_id = t.user_id)
     	left join utm_rep.users u on (u.id = t.user_id)
  	 
   	where
     	up.srvmode_id in (1353, 1629)
     	and u.unit_id in (%UNITS%)
  	 
   	GROUP BY t.user_id
  	 
   	UNION ALL
   	(SELECT city_number, 0 FROM all_numbers WHERE user_id IS NULL)



WITH pdh AS (

         	SELECT Min(discount_date) as discount_date, user_id, min(srvmode_id) srvmode_id, str_code
         	FROM (

            	SELECT discount_date, user_id, srvmode_id, str_code
            	FROM utm_Rep.prod_discount_history pdh
            	JOIN utm_rep.prod_serv_mode_costs psmc ON (psmc.id = pdh.srvmodecost_id)
            	JOIN utm_rep.products_services ps ON (ps.id = pdh.prod_code)

  	 
            	WHERE
            	discount_date BETWEEN to_date('%D0%', 'YYYYMMDD') AND to_date('%D1%', 'YYYYMMDD')
            	and
            	(
            	ps.str_code IN ('internet')
            	OR
            	srvmode_id IN (1566, 1565, 1567, 1559, 1560, 1561, 1640, 1574, 1554, 1553)
                     	 
            	)

            	and not exists (
              	select * from utm_Rep.prod_discount_history pdh1
              	JOIN utm_rep.prod_serv_mode_costs psmc1 ON (psmc1.id = pdh1.srvmodecost_id)
              	JOIN utm_rep.products_services ps1 ON (ps1.id = pdh1.prod_code)
              	where
              	To_Number(To_char(pdh1.discount_date, 'MMYYYY')) = To_Number(To_char(add_months(pdh.discount_date, -1), 'MMYYYY'))
              	AND pdh1.user_id = pdh.user_id
              	AND
              	(
                	psmc1.srvmode_id = psmc.srvmode_id         	 
                	OR
                	(ps1.str_code = 'internet'
                	AND ps1.str_code = ps.str_code)
              	)
              	)
                     	 
         	)
         	GROUP BY user_id, str_code

       	)

   	SELECT u.id user_id, pdh1.discount_date, u.user_block, u.unit_id,  Nvl2(pdh3.user_id, 1, 0) sell_status, Decode(pdh4.srvmode_id, 1553, 2, NULL, 0, 1) arenda_status, Nvl2(pdh3.discount_date, pdh3.discount_date, pdh4.discount_date) d_discount_date
   	FROM utm_rep.vw_users u


       	left JOIN (SELECT * FROM pdh WHERE str_code = 'internet') pdh1 ON (pdh1.user_id = u.id)
      	 
       	left JOIN (SELECT * FROM pdh WHERE str_code = 'sell') pdh3 ON (pdh3.user_id = u.id)
       	left JOIN (SELECT * FROM pdh WHERE str_code = 'rent') pdh4 ON (pdh4.user_id = u.id)  

      	 

     	WHERE
     	(pdh1.user_id IS NOT NULL
     	OR pdh3.user_id IS NOT NULL
     	or pdh4.user_id IS NOT NULL)
    	 
    	 
     	AND u.unit_id IN (%UNITS%)
     	AND u.is_juridical = 0



SELECT ahd.new_value as servpack_id, a.application_id, a.application_connectdate, u.id,
          	Nvl2(au.user_id, 1, 0) is_active,

      	(
      	SELECT Min(psm_name) from utm_rep.prod_discount_history pdh
      	JOIN utm_rep.prod_serv_mode_costs psmc ON (psmc.id = pdh.srvmodecost_id)
      	left JOIN utm_rep.prod_serv_modes psm ON (psm.id = psmc.srvmode_id)

      	WHERE
      	Trunc(pdh.discount_date) = Trunc(a.application_connectdate) AND u.id = pdh.user_id
      	AND pdh.prod_code = 4
      	AND ROWNUM = 1
      	) as tariff

      	FROM utm_rep.applications_b a


      	JOIN utm_rep.users u ON (u.id = a.application_clientid)

      	left JOIN utm_rep.current_active_users au ON (au.user_id = u.id)

      	left JOIN utm_rep.all_history ah ON (ah.user_id = u.id AND Trunc(ah.time_stamp) = Trunc(a.application_connectdate))
      	left JOIN utm_rep.all_history_details ahd ON (ahd.history_id = ah.history_id AND old_value = 0)


      	WHERE
        	Trunc(application_getdate) BETWEEN To_Date('01.12.2012', 'DD.MM.YYYY') AND To_Date('31.12.2012', 'DD.MM.YYYY')
        	AND application_agent = 'activesales_suburb'
        	AND u.unit_id = 1
 
WITH pdh AS (
         	SELECT discount_date, user_id, srvmode_id, prod_code FROM utm_Rep.prod_discount_history pdh
         	JOIN utm_rep.prod_serv_mode_costs psmc ON (psmc.id = pdh.srvmodecost_id)
  	 
         	WHERE discount_date BETWEEN to_date('01.01.2013', 'DD.MM.YYYY') AND sysdate
  	 
       	)  
  	 
  	 
   	SELECT pdh.user_id, pdh.discount_date, u.user_block, u.unit_id,  Nvl2(pdh3.user_id, 1, 0) sell_status, Decode(pdh4.srvmode_id, 1553, 2, NULL, 0, 1) arenda_status FROM pdh
       	left JOIN
       	(SELECT user_id FROM utm_Rep.prod_discount_history pdh2 WHERE prod_code = 4  AND discount_date BETWEEN to_date('01.12.2012', 'DD.MM.YYYY') and to_date('01.01.2013', 'DD.MM.YYYY')) pdh2  ON (pdh.user_id = pdh2.user_id)
      	 
       	JOIN utm_rep.vw_users u ON (u.id = pdh.user_id)
  	 
       	left JOIN (SELECT * FROM pdh WHERE srvmode_id IN (1566, 1565, 1567, 1559, 1560, 1561)) pdh3 ON pdh3.user_id = pdh.user_id
  	 
       	left JOIN (SELECT * FROM pdh WHERE srvmode_id IN (1640, 1574, 1554, 1553)) pdh4 ON pdh4.user_id = pdh.user_id
   	 
     	WHERE
     	pdh.prod_code = 4
    	 
     	AND pdh2.user_id IS NULL
