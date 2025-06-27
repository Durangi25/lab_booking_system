# Lab Booking System

A role-based web application developed to manage university lab bookings, equipment, and usage logs efficiently. This system supports different user roles with tailored dashboards and automates lab scheduling, approvals, and equipment tracking.

## Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [User Roles](#user-roles)
- [Database Schema](#database-schema)
- [Technologies Used](#technologies-used)
- [How to Run](#how-to-run)
- [Sample Data](#sample-data)
- [GitHub Repository](#github-repository)
- [Author](#author)

## Project Overview

The **Lab Booking System** is built to streamline the process of booking labs by instructors, approving or rejecting requests by lecturers, and allowing students to view lab schedules and equipment. Technical Officers (TOs) manage lab equipment and usage logs.

## Features

- Role-based login system: Instructor, Lecturer, Student, Technical Officer
- Instructors can submit lab booking requests
- Lecturers can approve or reject booking requests
- Students can view lab schedules and lab equipment
- Technical Officers can update equipment and view lab usage logs
- Automated logging of past lab sessions using scheduled database event

## User Roles

| Role             | Features                                                                 |
|------------------|--------------------------------------------------------------------------|
| **Instructor**   | Send lab booking requests with date, time, lab type                      |
| **Lecturer**     | View booking requests, approve/reject based on own profile               |
| **Student**      | View their lab schedule and the equipment available in the assigned labs |
| **Technical Officer (TO)** | Manage lab equipment data, view lab usage logs                      |

## Database Schema

The main tables in the system are:

- `instructor`
- `lecture`
- `student`
- `to` (Technical Officer)
- `lab`
- `lab_booking`
- `lab_shedule`
- `lab_equipment`
- `lab_usage_log`

> ✔️ *Includes foreign key relationships and event scheduler to log lab usage automatically.*

## Technologies Used

- **Frontend**: HTML, CSS, Bootstrap
- **Backend**: PHP (8.2.12)
- **Database**: MySQL (MariaDB 10.4.32)
- **Development Tool**: XAMPP (with phpMyAdmin 5.2.1)

## How to Run

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/lab-booking-system.git
