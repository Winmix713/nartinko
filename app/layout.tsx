import './globals.css'
import { Inter } from 'next/font/google'
import { UserProvider } from '@/contexts/UserContext'

const inter = Inter({ subsets: ['latin'] })

export const metadata = {
  title: 'WinMix Prediction System',
  description: 'Football match prediction system',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="hu">
      <head>
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
      </head>
      <body className={`${inter.className} flex flex-col min-h-screen bg-[#0A0A0A] text-white`}>
        <UserProvider>
          {children}
        </UserProvider>
      </body>
    </html>
  )
}

