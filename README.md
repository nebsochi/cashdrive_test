HOW TO USE ENDPOINTS CREATED

BASE URL = http://34.66.141.108/test/public/index.php/api

PROCESSES

1. CREATE USER - POST {{BASE URL}}/user/register 

{
    "name":"Folake",
    "email":"nebs@gmail.com",
    "phone":"08136932982",
    "password":"nebsochi"
}

2. LOGIN USER - POST {{BASE URL}}/user/login

{
    "email":"nebs@gmail.com",
    "password":"nebsochi"
}

3. ADD CARD - (token needed)

    i. POST {{BASE URL}}/card 
    (tokenize card by charging the card N5) NB: (for the reference to be verified in next endpoint, the authorization url returned must be used to make payment)
    
    {
        "email":"sochineme@gmail.com",
        "amount":"500"
    }
    
    ii. GEt {{BASE URL}}/card/{reference} (reference gotten from endpoint 3i above)
    
    {
        "email":"sochineme@gmail.com",
        "amount":"500"
    }

4. CREATE LOAN - POST {{BASE URL}}/loan  (token needed)
{
    "principal":"50000",
    "monthly_tenure":3,
    "monthly_interest":5
}

5. ACTIVATE LOAN - PUT {{BASE URL}}/loan/{loan_request_id}/activate  (meant to be done by an admin but I didn't create an admin user so no need to pass token here)

6. cron job runs in background after activating a loan and charges cards that have due loans and if a card is not billed, a penalty is added. If billed due date of schedule is updated to +30 days

7. GET LOAN WITH SCHEDULE(S) TO CONFIRM THE CRON JOBS WORK AS EXPECTED

    i. GET LOAN - GET {{BASE URL}}/loan/{loan_request_id}  (token needed)




