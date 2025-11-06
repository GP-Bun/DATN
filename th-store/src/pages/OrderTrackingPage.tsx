import { useState } from 'react'

export default function OrderTrackingPage() {
  const [code, setCode] = useState('')
  const [result, setResult] = useState<null | { status: string; steps: string[] }>(null)

  const onTrack = (e: React.FormEvent) => {
    e.preventDefault()
    // Demo giả lập trạng thái đơn hàng
    setResult({
      status: 'Đang giao',
      steps: [
        'Đã tiếp nhận đơn hàng',
        'Đã đóng gói',
        'Đang vận chuyển',
        'Dự kiến giao: 1-3 ngày',
      ]
    })
  }

  return (
    <div className="main">
      <h1>Theo dõi đơn hàng</h1>
      <form onSubmit={onTrack} className="checkout-form" style={{maxWidth:480}}>
        <input placeholder="Nhập mã đơn hàng" value={code} onChange={e=>setCode(e.target.value)} required />
        <button type="submit">Tra cứu</button>
      </form>
      {result && (
        <div style={{marginTop:24}}>
          <h2>Trạng thái: {result.status}</h2>
          <ul>
            {result.steps.map((s,i)=>(<li key={i}>{s}</li>))}
          </ul>
        </div>
      )}
    </div>
  )
}


