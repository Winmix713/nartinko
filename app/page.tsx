'use client'

import { useState } from 'react'
import Header from '@/components/Header'
import Footer from '@/components/Footer'
import MainContent from '@/components/MainContent'
import Timer from '@/components/Timer'
import { useUser } from '@/contexts/UserContext'

export default function Home() {
  const [timeLeft, setTimeLeft] = useState(375) // 6 minutes and 15 seconds
  const { user } = useUser()

  return (
    <>
      <Header />
      <Timer initialTime={timeLeft} onTimeUpdate={setTimeLeft} />
      <MainContent timeLeft={timeLeft} />
      <Footer />
    </>
  )
}

