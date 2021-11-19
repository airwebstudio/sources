import React, { useState, useEffect } from 'react'
import { connect } from 'react-redux'

import { withRouter } from 'react-router-dom'
import { withAuthUser, withRefreshToken } from 'react-auth-kit'

import { useSelector, useDispatch } from 'react-redux'
import { Link } from 'react-router-dom'
import { stripe as stripe_action} from '../../../actions'

import Header from '../../../components/Header'
import Footer from '../../../components/Footer'


const Wallet = props => {
    // const stripe = useStripe()
    // const elements = useElements()


    const [error, setError] = useState(false)
    const [payout_amount, setPayoutAmount] = useState(100)
    const [price, setPrice] = useState(40)
    const [seller, setSeller] = useState(1)
    const [amount, setAmount] = useState(20)
    const [url, setUrl] = useState('')
    const [card_data, setCardData] = useState({number: '', exp_month: '', exp_year: '', cvc: ''})

    const stripe = useSelector(
      state => state.stripe
    )

    const dispatch = useDispatch()

    const refreshData = () => {
      dispatch(stripe_action.stripe_balance())
      dispatch(stripe_action.stripe_history())
      dispatch(stripe_action.stripe_acc())
    }
    
    useEffect(() => {
      refreshData()

    }, [])
  
    const updateCardData = (data) => {
      let new_data = {...card_data, ...data}
      setCardData(new_data)
    }

    
    const startInterval = (qid, type) => {

      let opened_window = false
      setTimeout(async () => {
   
        const res = await dispatch(stripe_action.stripe_payment_status(qid, type))

        if (res.payload.error_data) 
          setError(res.payload.error_data)

        if (res.payload.status == 'AuthDone')
          setUrl('')

        if (res.payload.url && !opened_window) {
            opened_window = true
            setUrl(res.payload.url)
        }

        if ((res.payload.status == 'Done') || (res.payload.status == 'Fail') || (res.payload.status == 'Reserved')) {
            setUrl('')
            refreshData()
        }
        else {
          startInterval(qid, type)
        }

        

        
      }, 2000)
    }

    const handleSubmit3 = async (event) => {
      event.preventDefault()

      const payment = await dispatch(stripe_action.stripe_payout(payout_amount))
      startInterval(payment.payload.qid, payment.payload.type)

    }

    const handleSubmit2 = async (event) => {
      event.preventDefault()
      const payment = await dispatch(stripe_action.stripe_transaction(price, seller))
      //startInterval(payment.payload.qid, payment.payload.type)

      console.log('payment', payment)

      refreshData()

    }

    const joinMeeting = async (e, meeting_id) => {
      e.preventDefault()
      const meeting = await dispatch(stripe_action.join_meeting({meeting_id: meeting_id}))
      if (meeting.payload.error) {
        setError(meeting.payload.error)
      }
      else {
        startInterval(meeting.payload.qid, meeting.payload.type)
      }
    }

    const finishMeeting = async (e, meeting_id) => {
      e.preventDefault()

      const meeting = await dispatch(stripe_action.finish_meeting({meeting_id: meeting_id}))
      if (meeting.payload.error) {
        setError(meeting.payload.error)
      }
      else {

        for (let ind in meeting.payload.qids) {
          startInterval(meeting.payload.qids[ind], meeting.payload.type)
        }
        
      }
    }




    const handleSubmit = async (event) => {
      event.preventDefault()
  
      // if (!stripe || !elements) {
      //   return;
      // }
      // const cardElement = elements.getElement(CardElement);
  
      // const {error, paymentMethod} = await stripe.createPaymentMethod({
      //   type: 'card',
      //   card: cardElement,
      // });

      // if (error) {
      //   console.log('[error]', error);
      // } else {
      //   console.log('[PaymentMethod]', paymentMethod);
      // }

      // const token = await stripe.createToken(cardElement)
      const payment = await dispatch(stripe_action.stripe_payment(card_data, amount))
      if (payment.payload.error) {
        setError(payment.payload.error)
      }
      else {
        startInterval(payment.payload.qid, payment.payload.type)
      }
      
      
      //dispatch(stripe_action.stripe_balance())

      //stripe.confirmCardPayment(payment.payload.client_secret) //can be comfirm on client side
    //   if (payment.payload.receipt_url)
    //     window.location = payment.payload.receipt_url


    }

  
    return (
    <>

        <Header />
          { (stripe.payment_status) && <h2 style={{position: 'fixed', top: '50px', background: '#fff'}}>{stripe.payment_status.status}</h2> }
          { (error) && <h2 style={{position: 'fixed', top: '100px', background: 'red'}}>{error}</h2> }
          
          { (stripe.balance.status) &&
          <h1>This user's balance: 
            {(stripe.balance.available) && <div> avilable: ${stripe.balance.available }</div> } 
            {(stripe.balance.pending) && <div>pending: ${stripe.balance.pending }</div>} 
            {(stripe.balance.reserved) && <div>reserved: ${stripe.balance.reserved }</div>} 
          </h1>} 
        
          { (stripe.history.charge) &&
          <>
            <h1>Charge buyer history</h1>
            <ul>

            {stripe.history.charge.map(hitem =>                    
                  <li> Status {hitem.status}. Available on: {hitem.available_on}. Amount: ${hitem.amount}. Comission: ${hitem.fee}</li>             
            )}

            </ul>
          </>
          }

          { (stripe.history.buying) &&
            <>
              <h1>Buying history</h1>
              <ul>

              {stripe.history.buying.map(hitem =>                    
                    <li> {hitem.created_at}. Amount: ${hitem.amount}. Seller id: {hitem.seller_account_id}</li>             
              )}
            
              
              </ul>
            </>
            }
          

          { (stripe.history.selling) &&
          <>
            <h1>Selling history</h1>
            <ul>

            {stripe.history.selling.map(hitem =>                    
                  <li> {hitem.created_at}. Amount: ${hitem.amount}. Seller id: {hitem.seller_account_id}</li>           
            )}
            
            
            </ul>
          </>

          }

          { (stripe.history.payout) &&
          <>
            <h1>Payouts history</h1>
            <ul>

            {stripe.history.payout.map(hitem =>                    
                  <li> Status {hitem.status}. Available on: {hitem.available_on}. Amount: ${hitem.amount}. Comission: ${hitem.fee}</li>             
            )}
            
            
            </ul>
          </>
          }

          {/* End history */}


            <h1>Charge my balance</h1>

            
            { (url !== '') && <iframe src={url} height="720"></iframe>}

            <form onSubmit={handleSubmit}>
              <input type={"text"} placeholder={"Card no"} value={card_data.number}
                            onChange={(e)=>updateCardData({number: e.target.value})}/>
              <input type={"text"} placeholder={"Card month"} value={card_data.exp_month}
                            onChange={(e)=>updateCardData({exp_month: e.target.value})}/>
              <input type={"text"} placeholder={"Card year"} value={card_data.exp_year}
                            onChange={(e)=>updateCardData({exp_year: e.target.value})}/>
              <input type={"text"} placeholder={"Card CVC"} value={card_data.cvc}
                            onChange={(e)=>updateCardData({cvc: e.target.value})}/>
              <input type={"text"} placeholder={"Amount"} value={amount}
                            onChange={(e)=>setAmount(e.target.value)}/>

              <button type="submit">
                  Charge
              </button>
            </form>

            

            {/* <h1>Join meeting #3</h1>

            <form>


              <button onClick={(e) => joinMeeting(e, 3)} >
                    Join meeting now!
              </button>

              <button onClick={(e) => finishMeeting(e, 3)} >
                    Finish meeting #3
              </button>

            </form> */}


            {/* <form onSubmit={handleSubmit2}>
              <input type={"text"} placeholder={"Price"} value={price}
                            onChange={(e)=>setPrice(e.target.value)}/>
              
              <input type={"text"} placeholder={"Seller id"} value={seller}
                            onChange={(e)=>setSeller(e.target.value)}/>
              
              <button type="submit" >
                  Buy
              </button>
            </form> */}


            

          {(stripe.account.data) &&
            ((stripe.account.data.verificated) ? 
              <>
              
          
              <h1>Payout</h1>
              <form onSubmit={handleSubmit3}>
                <input type={"text"} placeholder={"Amount"} value={payout_amount}
                              onChange={(e)=>setPayoutAmount(e.target.value)}/>

                <button type="submit" >
                    Payout
                </button>
              </form>

              </>
            :
            <>If you want to payout from this account you have to pass <Link to="/stripe/reAuth">OnBoarding Proccess</Link></>
          
          )}
        <Footer/>
    </>
    )
  }


// const mapDispatchToProps = dispatch => ({
//     payment: (card_data, amount) => dispatch(stripe['stripe_payment'](card_data, amount)),
//     transaction: () =>  dispatch(stripe['stripe_transaction']()),
//     balance: () =>  dispatch(stripe['stripe_balance']()),
// })

// const mapStateToProps = state => {
//   console.log(state) 
//   return state
// }

export default withAuthUser(withRouter(withRefreshToken(Wallet)))