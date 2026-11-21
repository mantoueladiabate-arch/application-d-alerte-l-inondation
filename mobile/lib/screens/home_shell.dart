import 'package:flutter/material.dart';

import 'alerts_list_screen.dart';
import 'report_screen.dart';

class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _index = 0;
  final _alertsListKey = GlobalKey<AlertsListScreenState>();

  late final _screens = [
    const ReportScreen(),
    AlertsListScreen(key: _alertsListKey),
  ];

  void _onDestinationSelected(int i) {
    setState(() => _index = i);
    if (i == 1) _alertsListKey.currentState?.refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _screens),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: _onDestinationSelected,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.report_problem_outlined),
            selectedIcon: Icon(Icons.report_problem),
            label: 'Signaler',
          ),
          NavigationDestination(
            icon: Icon(Icons.list_alt_outlined),
            selectedIcon: Icon(Icons.list_alt),
            label: 'Alertes récentes',
          ),
        ],
      ),
    );
  }
}
