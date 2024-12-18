'use client'

import { useState, useEffect } from 'react'
import { useUser } from '@/contexts/UserContext'
import Header from '@/components/Header'
import Footer from '@/components/Footer'
import Statistics from '@/components/Statistics'

export default function StatisticsPage() {
  const { user } = useUser()
  const [predictions, setPredictions] = useState([])

  useEffect(() => {
    // In a real app, you'd fetch the predictions from an API or local storage
    const storedPredictions = localStorage.getItem('predictions')
    if (storedPredictions) {
      setPredictions(JSON.parse(storedPredictions))
    }
  }, [])

  return (
    <>
      <Header />
      <main className="container mx-auto px-4 py-8 flex-grow">
        <h1 className="text-3xl font-bold mb-6 text-[#CCFF00]">Statisztikák</h1>
        <Statistics predictions={predictions} />
      </main>
      <Footer />
    </>
  )
}

