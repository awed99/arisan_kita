import { useState, useEffect, useRef } from 'react';
import { Toastr, ToastrRef, ToastrProps } from '@paljs/ui/Toastr';
import { useRouter } from 'next/router'
import { Button } from '@paljs/ui/Button';
import { InputGroup } from '@paljs/ui/Input';
import { Checkbox } from '@paljs/ui/Checkbox';
import React from 'react';
import Link from 'next/link';
import Spinner from '@paljs/ui/Spinner';
import Progress from '@paljs/ui/ProgressBar';

import Auth, { Group } from 'components/Auth';
import Socials from 'components/Auth/Socials';
import Layout from 'Layouts';

import { validateEmail, checkPassword } from 'src/helpers/hooks';
import { size } from 'lodash';

export default function ResetPassword() {
  const router = useRouter()
  const toastrRef = useRef<ToastrRef>(null);
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [isError, setIsError] = useState(false)
  const [progress, setProgress] = useState(0)
  const [values, setValues] = useState({
    password: '',
    confirmPassword: '',
  })

  const handleChange = prop => event => {
    setValues({ ...values, [prop]: event.target.value })
  }

  useEffect(() => {
    if (size(router?.query?.token) > 0) {
      const token = router?.query?.token ?? ''
      setEmail(token)
    }
  }, [router?.query])

  const handleSubmit = (event:any) => {
    event.preventDefault()
    
    setLoading(true)
    const _data = new FormData()
    _data.append('token', email.trim())
    _data.append('password', values?.password.trim())
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/change_password`,
    {
      method: 'POST',
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
      if (res?.status === '000') { 
        toastrRef.current?.add(res?.message, 'Success', {
          position: 'topEnd',
          status: 'Success',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
        setValues({
          password: '',
          confirmPassword: '',
        })
        setTimeout(() => window.location.href='/auth/login', 3000)
      } else {
        toastrRef.current?.add(res?.message, 'Danger', {
          position: 'topEnd',
          status: 'Danger',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
      }

      setTimeout(() => setLoading(false), 3000)
    })
    .catch(() => setLoading(false))
  }

  useEffect(() => {
    let _isError = 0
    if (checkPassword(values?.password)?.error === 'Weak!') {
      _isError++
    }
    if (checkPassword(values?.confirmPassword)?.error === 'Weak!' || values?.confirmPassword !== values?.password) {
      _isError++
    }

    setProgress((2-_isError)/2*100)
    if (_isError > 0) {
      setIsError(true)
    } else {
      setIsError(false)
    }
  }, [values])

  return (
    <Layout title="Change Password">
      <Auth title="Change Password" subTitle="Please set a new password">
        <form onSubmit={e => handleSubmit(e)}>
          <InputGroup fullWidth status={(checkPassword(values?.password)?.error === 'Weak!') ? 'Warning' : 'Success'}>
            <input type="password" onChange={handleChange('password')} placeholder="Password" />
            {/* <span>{checkPassword(values?.password)?.error || checkPassword(values?.password)?.valid}</span> */}
          </InputGroup>
          <InputGroup fullWidth status={(checkPassword(values?.confirmPassword)?.error === 'Weak!' || values?.confirmPassword !== values?.password) ? 'Warning' : 'Success'}>
            <input type="password" onChange={handleChange('confirmPassword')} placeholder="Confirm Password" />
          </InputGroup>
          <Button status="Success" type="submit" shape="SemiRound" fullWidth disabled={isError || loading}>
            Change Password
          </Button>
        </form>
        <Group>
          <Link href="/auth/login">
            <a>Back to Log In</a>
          </Link>
          <Link href="/auth/register">
            <a>Register</a>
          </Link>
        </Group>
      </Auth>
      <Toastr ref={toastrRef} />
      {loading && <Spinner size="Large" status="Danger" />}
    </Layout>
  );
}
