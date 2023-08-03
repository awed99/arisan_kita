import React from 'react';
import Layout from 'Layouts';
import Row from '@paljs/ui/Row';
import Col from '@paljs/ui/Col';
import { Card, CardBody, CardHeader, CardFooter } from '@paljs/ui/Card';
import { List, ListItem } from '@paljs/ui/List';
import User from '@paljs/ui/User';

const Home = () => {
  const userList = [
    { name: 'Ibu-ibu Sosialita', title: 'Miss. Chen' },
    { name: 'Sosialita Jaksel', title: 'Ny. Yuan' },
    { name: 'Mahmud JakPus', title: 'Nn. Marijem' },
    { name: 'Macan Kemayoran', title: 'Ibu Marsinah' },
    { name: 'Ibu Muda Kayuringin', title: 'Si Cantik' },
  ];

  return <Layout title="Home">
    <Row>
      <Col breakPoint={{ xs: 12, md: 8 }}>
        <Card status="Info">
          <CardHeader>Daftar Room Arisan</CardHeader>
          <CardBody sx={{p:10}}>
            <List>
              {userList.map((user, index) => (
                <ListItem key={index}>
                  <User title={user.title} name={user.name} />
                </ListItem>
              ))}
            </List>
            
          </CardBody>
          {/* <CardFooter>Footer</CardFooter> */}
        </Card>
      </Col>
      
      <Col breakPoint={{ xs: 12, md: 4 }}>
        <Card status="Primary">
          <CardHeader>Pengguna Baru</CardHeader>
          <CardBody sx={{p:10}}>
            <List>
              {userList.map((user, index) => (
                <ListItem key={index}>
                  <User title={user.title} name={user.name} />
                </ListItem>
              ))}
            </List>
            
          </CardBody>
          {/* <CardFooter>Footer</CardFooter> */}
        </Card>
      </Col>
    </Row>
  </Layout>;
};
export default Home;
