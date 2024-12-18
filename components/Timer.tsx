'use client'

import { useState, useEffect } from 'react'

interface TimerProps {
  initialTime: number
  onTimeUpdate: (timeLeft: number) => void
}

export default function Timer({ initialTime, onTimeUpdate }: TimerProps) {
  const [timeLeft, setTimeLeft] = useState(initialTime)

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft((prevTime) => {
        const newTime = prevTime > 0 ? prevTime - 1 : 0
        return newTime
      })
    }, 1000)

    return () => clearInterval(timer)
  }, [])

  useEffect(() => {
    onTimeUpdate(timeLeft)
  }, [timeLeft, onTimeUpdate])

  const minutes = Math.floor(timeLeft / 60)
  const seconds = timeLeft % 60

  return (
    <div className="fixed top-4 right-4 bg-[#CCFF00] text-black px-4 py-2 rounded-md font-bold">
      {`${minutes}:${seconds.toString().padStart(2, '0')}`}
    </div>
  )
}

